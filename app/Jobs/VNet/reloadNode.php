<?php

namespace App\Jobs\VNet;

use App\Models\Node;
use Arr;
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

class reloadNode implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Collection $nodes;

    public function __construct(Collection|Node $nodes)
    {
        if ($nodes instanceof Collection) {
            $this->nodes = $nodes;
        } else {
            $this->nodes = new Collection([$nodes]);
        }
    }

    public function handle(): array
    {
        foreach ($this->nodes as $node) {
            $data = $node->getSSRConfig();

            if ($node->is_ddns) {
                $result = ['list' => $node->server];
                if (! $this->send($node->server.':'.$node->push_port, $node->auth->secret, $data)) {
                    $result['error'] = [$node->server];
                }
            } else { // 多IP支持
                $result = ['list' => $node->ips()];
                foreach ($result['list'] as $ip) {
                    if (! $this->send($ip.':'.$node->push_port, $node->auth->secret, $data)) {
                        $result['error'][] = $ip;
                    }
                }
            }
        }

        return $result ?? [];
    }

    public function send(string $host, string $secret, array $data): bool
    {
        try {
            $response = Http::baseUrl($host)->timeout(15)->withHeader('secret', $secret)->post('api/v2/node/reload', $data);
            $message = $response->json();
            if ($message && Arr::has($message, ['success', 'content']) && $response->ok()) {
                if ($message['success'] === 'false') {
                    Log::warning("【重载节点】失败：$host 反馈：".$message['content']);

                    return false;
                }

                Log::notice("【重载节点】成功：$host 反馈：".$message['content']);

                return true;
            }
            Log::warning("【重载节点】失败：$host");
        } catch (Exception $exception) {
            Log::alert('【重载节点】推送异常：'.$exception->getMessage());
        }

        return false;
    }

    // 队列失败处理
    public function failed(Throwable $exception): void
    {
        Log::alert('【重载节点】推送异常：'.$exception->getMessage());
    }
}
