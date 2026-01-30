<?php

namespace App\Jobs\Hysteria2;

use App\Models\Node;
use App\Models\User;
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
            if ($node->is_ddns) {
                $data = $this->send($node->server.':'.$node->push_port, $node->auth->secret);
                if ($data) {
                    $this->syncTrafficData($node, $data);
                }
            } else { // 多IP支持
                foreach ($node->ips() as $ip) {
                    $data = $this->send($ip.':'.$node->push_port, $node->auth->secret);
                    if ($data) {
                        $this->syncTrafficData($node, $data);
                    }
                }
            }
        }
    }

    private function syncTrafficData(Node $node, array $data): void
    {
        $rate = $node->traffic_rate;
        foreach ($data as $userId => $stats) { // Hysteria2 API 返回的是用户ID
            $user = User::find($userId);

            if (! $user) {
                Log::warning("【Hysteria2流量统计】未找到ID为 {$userId} 的用户");

                continue;
            }

            $upload = (int) $stats['tx'] * $rate;
            $download = (int) $stats['rx'] * $rate;

            $user->update(['u' => $user->u + $upload, 'd' => $user->d + $download, 't' => time()]);

            $node->userDataFlowLogs()->create(['user_id' => $user->id, 'u' => $upload, 'd' => $download, 'traffic' => formatBytes($upload + $download), 'rate' => $rate, 'log_time' => time()]);
        }
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
