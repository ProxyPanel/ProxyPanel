<?php

namespace App\Jobs\VNet;

use Arr;
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

    private $nodes;

    public function __construct($nodes)
    {
        if (! $nodes instanceof Collection) {
            $nodes = collect([$nodes]);
        }
        $this->nodes = $nodes;
    }

    public function handle(): bool
    {
        $allSuccess = true;
        foreach ($this->nodes as $node) {
            $ret = $this->send(($node->server ?: $node->ip).':'.$node->push_port, $node->auth->secret, [
                'id' => $node->id,
                'port' => (string) $node->port,
                'passwd' => $node->passwd ?: '',
                'method' => $node->method,
                'protocol' => $node->protocol,
                'obfs' => $node->obfs,
                'protocol_param' => $node->protocol_param,
                'obfs_param' => $node->obfs_param ?: '',
                'push_port' => $node->push_port,
                'single' => $node->single,
                'secret' => $node->auth->secret,
                'speed_limit' => $node->getRawOriginal('speed_limit'),
                'is_udp' => $node->is_udp,
                'client_limit' => $node->client_limit,
                // 'redirect_url' => (string) sysConfig('redirect_url'),
            ]);

            if (! $ret) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    public function send($host, $secret, $data): bool
    {
        $response = Http::baseUrl($host)->timeout(15)->withHeaders(['secret' => $secret])->post('api/v2/node/reload', $data);
        $message = $response->json();
        if ($message && Arr::has($message, ['success', 'content']) && $response->ok()) {
            if ($message['success'] === 'false') {
                Log::warning('【重载节点】失败：'.$host.' 反馈：'.$message['content']);

                return false;
            }

            Log::notice('【重载节点】成功：'.$host.' 反馈：'.$message['content']);

            return true;
        }
        Log::warning('【重载节点】失败：'.$host);

        return false;
    }

    // 队列失败处理
    public function failed(Throwable $exception)
    {
        Log::error('【重载节点】推送异常：'.$exception->getMessage());
    }
}
