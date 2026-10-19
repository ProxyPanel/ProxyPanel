<?php

namespace App\Jobs\VNet;

use App\Models\Node;
use Arr;
use Exception;
use Http;
use Log;

class GetUser
{
    /**
     * 取节点上的用户集合.
     *
     * @param  string|null  $address  逐个寻址时指定主机（端口在这里补）；为空时沿用节点第一个可达地址
     * @return false|array false 只代表「拿不到集合」，节点上没人时返回空数组
     */
    public function list(Node $node, string $mode = 'uid', ?string $address = null): false|array
    {
        $host = ($address ?: $node->server ?: $node->ips()[0]).':'.$node->push_port;

        if (NodeReachability::unreachable($host)) { // 退避窗口内不再烧连接超时；返回值仍是「取不到集合」
            return false;
        }

        $list = $this->send($host, $node->auth->secret);

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

            if (! $response->ok()) { // 取不到集合，和「节点上没人」必须是两种返回值
                Log::warning('【用户列表】获取失败（推送地址：'.$host.'，状态：'.$response->status().'）');

                return false;
            }

            $message = $response->json();
            // 正常返回是用户对象数组；secret 不匹配等错误走 {"success":"false"} 信封，不能当成空列表
            if (! is_array($message) || ! array_is_list($message)) {
                Log::warning('【用户列表】返回内容不是用户数组（推送地址：'.$host.'，节点反馈：'.$this->reason($message, $response->body()).'）');

                return false;
            }

            return $message;
        } catch (Exception $exception) {
            NodeReachability::mark($host);
            Log::alert('【用户列表】获取异常：'.$exception->getMessage());
        }

        return false;
    }

    /**
     * 从节点响应里取一句可读的原因：优先失败信封的 content，否则用响应体开头，让日志能直接指出是鉴权还是端口不对.
     */
    private function reason(mixed $message, string $body): string
    {
        if (is_array($message) && isset($message['content'])) {
            return (string) $message['content'];
        }

        return mb_substr(trim(is_array($message) ? (string) json_encode($message, JSON_UNESCAPED_UNICODE) : $body), 0, 200);
    }
}
