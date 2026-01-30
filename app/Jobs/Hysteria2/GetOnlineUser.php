<?php

namespace App\Jobs\Hysteria2;

use App\Models\Node;
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

    private bool $isOffline = false;

    public function __construct(private Collection|Node $nodes)
    {
        if (! $nodes instanceof Collection) {
            $this->nodes = new Collection([$nodes]);
        }
    }

    public function handle(): void
    {
        foreach ($this->nodes as $node) {
            $totalOnlineUsers = 0;
            if ($node->is_ddns) {
                $totalOnlineUsers = $this->send($node->server.':'.$node->push_port, $node->auth->secret);
            } else { // 多IP支持
                foreach ($node->ips() as $ip) {
                    // 对于多IP节点，累加所有IP上的在线用户数
                    $totalOnlineUsers += $this->send($ip.':'.$node->push_port, $node->auth->secret);
                }
            }
            $node->onlineLogs()->create(['online_user' => $totalOnlineUsers, 'log_time' => time()]);

            // 如果成功获取在线用户数，说明节点正常，记录心跳
            if (! $this->isOffline) { // 即使没有在线用户也说明节点通信正常
                // 获取节点最后的心跳记录（在90秒内）以计算运行时间
                $lastHeartbeat = $node->heartbeats()
                    ->where('log_time', '>=', time() - 90)
                    ->latest('log_time')
                    ->first();

                if ($lastHeartbeat) {
                    // 如果之前有心跳记录且时间间隔不超过90秒，计算运行时间增量
                    $uptime = $lastHeartbeat->uptime + (time() - $lastHeartbeat->log_time);
                } else {
                    // 如果没有90秒内的心跳记录或间隔超过90秒，重新开始计时
                    $uptime = 60; // 重新开始计时，初始运行时间为60秒
                }

                $node->heartbeats()->create([
                    'uptime' => $uptime,
                    'load' => 'N/A',
                    'log_time' => time(),
                ]);
            }
        }
    }

    private function send(string $host, string $secret): int
    {
        try {
            $request = Http::baseUrl($host)->timeout(15)->withHeader('Authorization', $secret);
            $response = $request->get('/online');

            $data = $response->json();
            if ($data) {
                return count($data);
            }

            if (! $response->ok()) {
                $this->isOffline = true;
            }
        } catch (Exception $exception) {
            Log::alert('【在线用户】获取异常：'.$exception->getMessage());
        }

        return 0;
    }

    // 队列失败处理
    public function failed(Throwable $exception): void
    {
        Log::alert('【在线用户】获取异常：'.$exception->getMessage());
    }
}
