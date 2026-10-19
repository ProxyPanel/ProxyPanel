<?php

namespace App\Jobs\VNet;

use App\Jobs\VNet\Concerns\PushesToNodes;
use App\Models\Node;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class DelUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use PushesToNodes;
    use Queueable;
    use SerializesModels;

    private Collection $nodes;

    public function __construct(private readonly array|int $userIds, Collection|Node $nodes)
    {
        if ($nodes instanceof Collection) {
            $this->nodes = $nodes;
        } else {
            $this->nodes = new Collection([$nodes]);
        }
    }

    public $tries = 3;

    public $backoff = [30, NodeReachability::TTL];

    public function handle(): void
    {
        // 删除丢一次就是「已禁用的账号还能用」，比新增更不能只留一行日志
        $undelivered = $this->pushToNodes($this->nodes, $this->endpoint(), '删除用户', is_array($this->userIds) ? $this->userIds : null, 15);

        if ($undelivered !== []) {
            throw new Exception('【删除用户】未送达：'.implode(',', $undelivered));
        }
    }

    /** 单个 id 仍走 v1 端点：老部署留的口子，集合形式走 v2 批量端点 */
    private function endpoint(): string
    {
        return is_array($this->userIds) ? 'api/v2/user/del/list' : 'api/user/del/'.$this->userIds;
    }

    // 队列失败处理
    public function failed(Throwable $exception): void
    {
        Log::alert('【删除用户】推送异常：'.$exception->getMessage());
    }
}
