<?php

namespace App\Jobs\Hysteria2;

use App\Models\Node;
use App\Models\User;
use App\Utils\Hysteria2\OnlineReport;
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

class GetOnlineUser implements ShouldQueue
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
            // 每个节点各自成败：一个节点不可达或抛异常，不能影响同批次里其它节点的心跳
            try {
                $this->syncNode($node);
            } catch (Throwable $exception) {
                Log::alert("【在线用户】节点 {$node->id} 同步异常：".$exception->getMessage());
            }
        }
    }

    private function syncNode(Node $node): void
    {
        $secret = $node->auth?->secret;
        $hosts = $this->hosts($node);

        if ($hosts === [] || empty($secret)) { // 没有可轮询的地址或密钥：本次不采集，也不能当成在线
            Log::warning("【在线用户】节点 {$node->id} 缺少上报地址或密钥，本次不采集");

            return;
        }

        $responses = [];
        foreach ($hosts as $host) {
            $responses[] = $this->fetch($host, $secret);
        }

        $report = OnlineReport::merge($responses);

        if (! $report['reachable']) { // 全部地址都联系不上：不写心跳，节点按离线判定
            Log::alert("【在线用户】节点 {$node->id} 所有上报地址均不可达：".implode(', ', $hosts));

            return;
        }

        $onlineIds = $report['user_ids'];
        $users = OnlineReport::split($onlineIds, $this->eligibleUserIds($node, $onlineIds));

        // 先落本次观测结果：在线人数与心跳是节点列表/仪表盘读的状态，
        // 不能被后面的踢人推送（队列不可用时会抛异常）拖累
        $node->onlineLogs()->create(['online_user' => count($users['online']), 'log_time' => time()]);
        $this->recordHeartbeat($node);

        $this->syncUserPresence($users['online']);
        $this->evictRevokedUsers($node, $users['revoked']);
    }

    /**
     * 把观测到的在线用户同步进 user.t.
     *
     * t 是面板里「最近在线/有流量」的统一口径（仪表盘的在线用户数、用户列表的在线筛选、
     * 营销的活跃用户筛选都读它，其它协议由节点上报流量时写入）。Hysteria2 的在线用户此前完全不写 t，
     * 于是「连着但当前没有流量」的用户在面板里不算在线，两套口径长期对不上。
     * 用查询构造器批量更新：不触发模型事件，也不改动 updated_at。
     *
     * @param  array<int, int>  $userIds
     */
    private function syncUserPresence(array $userIds): void
    {
        if ($userIds === []) {
            return;
        }

        User::whereIn('id', $userIds)->update(['t' => time()]);
    }

    /**
     * 踢掉面板已经不放行的在线用户.
     *
     * 鉴权只在客户端连接时发生，禁用/过期/失去权限/已删除的用户会一直挂在节点上被计入在线人数。
     *
     * @param  array<int, int>  $userIds
     */
    private function evictRevokedUsers(Node $node, array $userIds): void
    {
        if ($userIds === []) {
            return;
        }

        DelUser::dispatch(array_map('strval', $userIds), $node);
        Log::info("【在线用户】节点 {$node->id} 踢下线已失去资格的用户", ['uids' => $userIds]);
    }

    /**
     * 该节点当前仍然放行的用户ID.
     *
     * 条件必须与 Hysteria2Controller::authenticate 完全一致（enable + 节点等级 + 用户分组）：
     * 比鉴权判定更严格的话，客户端会被反复「踢下线 -> 重连又被放行」。
     *
     * @param  array<int, int>  $userIds
     * @return array<int, int>
     */
    private function eligibleUserIds(Node $node, array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return User::whereEnable(1)
            ->whereIn('id', $userIds)
            ->where('level', '>=', $node->level)
            ->where(function ($query) use ($node) {
                $query->whereIn('user_group_id', $node->userGroups->pluck('id'))->orWhereNull('user_group_id');
            })
            ->pluck('id')
            ->all();
    }

    /**
     * 本次需要轮询的地址.
     *
     * @return array<int, string> host:port
     */
    private function hosts(Node $node): array
    {
        $port = (int) $node->push_port;

        if ($port < 1) { // 未配置上报端口时流量统计 API 无从访问
            return [];
        }

        if ($node->is_ddns) {
            return $node->server ? [$node->server.':'.$port] : [];
        }

        return array_map(static fn ($ip) => $ip.':'.$port, $node->ips()); // 多IP节点逐张网卡轮询
    }

    /**
     * 拉取一个地址的 /online.
     *
     * @return array|null 解码后的「用户ID => 连接数」映射；null 表示该地址不可达或响应无法解析
     */
    private function fetch(string $host, string $secret): ?array
    {
        try {
            $response = Http::baseUrl($host)->timeout(15)->withHeader('Authorization', $secret)->get('/online');
        } catch (Exception $exception) {
            Log::alert("【在线用户】节点 {$host} 请求异常：".$exception->getMessage());

            return null;
        }

        if (! $response->successful()) {
            Log::alert("【在线用户】节点 {$host} 返回异常状态：".$response->status());

            return null;
        }

        $data = $response->json();

        return is_array($data) ? $data : null;
    }

    /**
     * 记录一次存活观测.
     */
    private function recordHeartbeat(Node $node): void
    {
        $now = time();
        $previous = $node->heartbeats()->latest('log_time')->first();

        $node->heartbeats()->create([
            'uptime' => OnlineReport::uptime(
                $previous ? (int) $previous->uptime : null,
                $previous ? (int) $previous->log_time : null,
                $now
            ),
            'load' => 'N/A',
            'log_time' => $now,
        ]);
    }

    // 队列失败处理
    public function failed(Throwable $exception): void
    {
        Log::alert('【在线用户】获取异常：'.$exception->getMessage());
    }
}
