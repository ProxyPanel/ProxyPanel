<?php

namespace App\Jobs\VNet;

use Arr;
use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class reloadNode implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $nodes;

    public function __construct($nodes)
    {
        $this->nodes = $nodes;
    }

    public function handle(): bool
    {
        $allSuccess = true;
        foreach ($this->nodes as $node) {
            $ret = $this->send(($node->server ?: $node->ip).':'.$node->push_port, $node->auth->secret, [
                'id'             => $node->id,
                'port'           => (string) $node->port,
                'passwd'         => $node->passwd ?: '',
                'method'         => $node->method,
                'protocol'       => $node->protocol,
                'obfs'           => $node->obfs,
                'protocol_param' => $node->protocol_param,
                'obfs_param'     => $node->obfs_param ?: '',
                'push_port'      => $node->push_port,
                'single'         => $node->single,
                'secret'         => $node->auth->secret,
                //			'is_udp'         => $node->is_udp,
                //			'speed_limit'    => $node->speed_limit,
                //			'client_limit'   => $node->client_limit,
                //			'redirect_url'   => (string) sysConfig('redirect_url')
            ]);

            if (!$ret) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    public function send($host, $secret, $data): bool
    {
        $client = Http::baseUrl($host)->timeout(15)->withHeaders(['secret' => $secret]);

        $response = $client->post('api/v2/node/reload', $data);
        if ($response->ok()) {
            $message = $response->json();
            if (Arr::has($message, ['success', 'content'])) {
                if ($message['success']) {
                    return true;
                }
                Log::error('重载节点失败：'.$host.' 反馈：'.$message['content']);
            }
        }
        Log::error('重载节点失败url: '.$host);

        return false;
    }
}
