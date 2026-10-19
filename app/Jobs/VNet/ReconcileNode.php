<?php

namespace App\Jobs\VNet;

use App\Models\Node;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

/**
 * 按「面板认为该在线的账号」与「节点上实际的账号」求差，缺的补、多的删.
 *
 * 节点只在启动与重载时各拉一次用户列表，推送是唯一的增量通道；一次推送失败（节点重启、队列积压、
 * 网络抖动）就会让已禁用或已到期的账号长期留在节点上继续可用。这里只做增删、不 reload，
 * 因此可以定时跑而不断掉在线连接。
 */
class ReconcileNode implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;

    public $backoff = 60;

    public function __construct(private readonly Node $node)
    {
    }

    public function handle(): void
    {
        // 基准必须是 Node::users()：它同时是 userList 端点的取数查询，另写一份过滤条件就会再次漂移
        $expected = array_map('intval', $this->node->users()->pluck('id')->all());

        foreach ($this->node->pushAddresses() as $address) {
            $actual = (new GetUser)->list($this->node, 'uid', $address);

            if ($actual === false) { // 取不到集合不等于「节点上没人」，否则会把整节点当成缺账号
                Log::warning('【VNET 对账】节点 #'.$this->node->id.' 取不到 '.$address.' 的用户集合，本轮跳过');

                continue;
            }

            ['add' => $add, 'del' => $del] = static::diff($expected, array_map('intval', $actual));

            if ($add !== []) {
                AddUser::dispatch($add, $this->node);
            }

            if ($del !== []) {
                DelUser::dispatch($del, $this->node);
            }

            if ($add !== [] || $del !== []) {
                Log::notice('【VNET 对账】节点 #'.$this->node->id.'（'.$address.'）补齐 '.count($add).' 个、移除 '.count($del).' 个账号');
            }
        }
    }

    /**
     * 纯 uid 差集：只比集合成员，两边的先后顺序不参与判据.
     *
     * @param  array<int, int>  $expected
     * @param  array<int, int>  $actual
     * @return array{add: array<int, int>, del: array<int, int>}
     */
    public static function diff(array $expected, array $actual): array
    {
        return [
            'add' => array_values(array_diff($expected, $actual)),
            'del' => array_values(array_diff($actual, $expected)),
        ];
    }

    // 队列失败处理
    public function failed(Throwable $exception): void
    {
        Log::alert('【VNET 对账】节点 #'.$this->node->id.' 对账异常：'.$exception->getMessage());
    }
}
