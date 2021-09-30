<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Models\Node;
use Illuminate\Http\JsonResponse;

class SSRController extends BaseController
{
    // 获取节点信息
    public function getNodeInfo(Node $node): JsonResponse
    {
        return $this->returnData('获取节点信息成功', 'success', 200, [
            'id'           => $node->id,
            'method'       => $node->method,
            'protocol'     => $node->protocol,
            'obfs'         => $node->obfs,
            'obfs_param'   => $node->obfs_param ?? '',
            'is_udp'       => $node->is_udp,
            'speed_limit'  => $node->getRawOriginal('speed_limit'),
            'client_limit' => $node->client_limit,
            'single'       => $node->single,
            'port'         => (string) $node->port,
            'passwd'       => $node->passwd ?? '',
            'push_port'    => $node->push_port,
            'secret'       => $node->auth->secret,
            'redirect_url' => sysConfig('redirect_url'),
        ]);
    }

    // 获取节点可用的用户列表
    public function getUserList(Node $node): JsonResponse
    {
        foreach ($node->users() as $user) {
            $data[] = [
                'uid'         => $user->id,
                'port'        => $user->port,
                'passwd'      => $user->passwd,
                'method'      => $user->method,
                'protocol'    => $user->protocol,
                'obfs'        => $user->obfs,
                'obfs_param'  => $node->obfs_param,
                'speed_limit' => $user->getRawOriginal('speed_limit'),
                'enable'      => $user->enable,
            ];
        }

        return $this->returnData('获取用户列表成功', 'success', 200, $data ?? [], ['updateTime' => time()]);
    }
}
