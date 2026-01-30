<?php

namespace App\Jobs\VNet;

use App\Models\Node;
use Arr;
use Exception;
use Http;
use Log;

class GetUser
{
    public function list(Node $node, string $mode = 'uid'): false|array
    {
        $list = $this->send(($node->server ?: $node->ips()[0]).':'.$node->push_port, $node->auth->secret);

        if (is_array($list)) {
            if ($mode === 'uid') {
                return Arr::pluck($list, 'uid');
            }

            if ($mode === 'port') {
                return Arr::pluck($list, 'port');
            }

            return $list;
        }

        return false;
    }

    private function send(string $host, string $secret): false|array
    {
        try {
            $response = Http::baseUrl($host)->timeout(20)->withHeader('secret', $secret)->get('api/user/list');
            $message = $response->json();
            if ($message && $response->ok()) {
                return $message;
            }

            Log::warning('【用户列表】获取失败（推送地址：'.$host.'）');
        } catch (Exception $exception) {
            Log::alert('【用户列表】获取异常：'.$exception->getMessage());
        }

        return false;
    }
}
