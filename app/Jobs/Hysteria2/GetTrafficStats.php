<?php

namespace App\Jobs\Hysteria2;

use App\Models\Node;
use App\Models\User;
use App\Models\UserDataFlowLog;
use App\Utils\NodeTraffic\LockRetry;
use App\Utils\NodeTraffic\TrafficBatch;
use DB;
use Exception;
use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class GetTrafficStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Collection|Node $nodes)
    {
        if (! $nodes instanceof Collection) {
            $this->nodes = new Collection([$nodes]);
        }
    }

    public function handle(): void
    {
        foreach ($this->nodes as $node) {
            // 每个节点各自成败：一个节点异常不能让同批次后面的节点这一分钟完全不采流量
            try {
                $this->syncNode($node);
            } catch (Throwable $exception) {
                Log::alert("【Hysteria2流量统计】节点 {$node->id} 同步异常：".$exception->getMessage());
            }
        }
    }

    private function syncNode(Node $node): void
    {
        $secret = $node->auth?->secret;

        if (empty($secret)) { // 没有密钥无法读取流量统计 API
            Log::warning("【Hysteria2流量统计】节点 {$node->id} 缺少密钥，本次不采集");

            return;
        }

        $records = [];
        foreach ($node->is_ddns ? [$node->server] : $node->ips() as $host) { // 多IP支持
            foreach ($this->send($host.':'.$node->push_port, $secret) as $userId => $stats) {
                $records[] = ['uid' => $userId, 'upload' => $stats['tx'] ?? 0, 'download' => $stats['rx'] ?? 0];
            }
        }

        $this->flushTraffic($node, $records);
    }

    /**
     * 把采集到的增量落成流量记录并累加到账号上.
     *
     * 累加必须发生在数据库侧（u = u + x）：一个节点的多个地址、以及下一分钟的轮询都会写同一个账号，
     * 读回来加完再写回会丢掉其中一份。
     */
    private function flushTraffic(Node $node, array $records): void
    {
        if ($records === []) { // 各地址都不可达或无人产生流量
            return;
        }

        $rate = (float) $node->traffic_rate;
        $logTime = time();
        $traffic = TrafficBatch::aggregate($records, $rate);

        // 一次查出真正存在的账号：节点侧残留的已删账号只记日志并跳过，不连累同批其他用户的流量
        $existsIds = array_map('intval', User::whereIn('id', array_keys($traffic))->pluck('id')->all());
        if ($missingIds = array_values(array_diff(array_keys($traffic), $existsIds))) {
            // 只记前若干个：上报内容来自节点，不能让它把日志写爆
            Log::warning("【Hysteria2流量统计】节点 {$node->id} 上报了不存在的账号，已跳过：".implode(',', array_slice($missingIds, 0, 10)).'（共 '.count($missingIds).' 条）');
        }

        $traffic = array_intersect_key($traffic, array_flip($existsIds));

        if ($traffic === []) {
            return;
        }

        // 与 CoreController 的上报写入同一条锁序判据，也共用死锁重跑：这条路径拉的是 /traffic?clear=1，
        // 节点在响应里就把计数清了，写失败没有任何重投可言
        LockRetry::run(fn () => DB::transaction(function () use ($node, $traffic, $rate, $logTime): void {
            // 先累加再插记录：插子表会经外键给 user 行加 S 锁，同事务再把 S 升成 X 就是并发上报的死锁源
            foreach (TrafficBatch::chunks($traffic) as $chunk) { // 每块一条 UPDATE
                $query = TrafficBatch::buildUpdateQuery($chunk, $logTime);
                DB::update($query['sql'], $query['bindings']);
            }

            UserDataFlowLog::insert(TrafficBatch::buildLogRows($traffic, $node->id, $rate, $logTime));
        }));
    }

    private function send(string $host, string $secret): array
    {
        try {
            $request = Http::baseUrl($host)->timeout(15)->withHeader('Authorization', $secret);
            $response = $request->get('/traffic?clear=1');

            $data = $response->json();
            if ($data) {
                return $data;
            }
        } catch (Exception $exception) {
            Log::alert('【Hysteria2流量统计】获取异常：'.$exception->getMessage());
        }

        return [];
    }

    // 队列失败处理
    public function failed(Throwable $exception): void
    {
        Log::alert('【Hysteria2流量统计】获取异常：'.$exception->getMessage());
    }
}
