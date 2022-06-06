<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Models\Node;
use Illuminate\Http\JsonResponse;

class SSController extends CoreController
{
    // 获取节点信息
    public function getNodeInfo(Node $node): JsonResponse
    {
        $data = [
            'id'           => $node->id,
            'method'       => $node->profile['method'],
            'speed_limit'  => $node->getRawOriginal('speed_limit'),
            'client_limit' => $node->client_limit,
            'redirect_url' => sysConfig('redirect_url'),
        ];

        if ($node->profile['passwd']) {
            $data['port'] = $node->port;
        }

        return $this->returnData('获取节点信息成功', 200, 'success', $data);
    }

    // 获取节点可用的用户列表
    public function getUserList(Node $node): JsonResponse
    {
        foreach ($node->users() as $user) {
            $data[] = [
                'uid'         => $user->id,
                'port'        => $user->port,
                'passwd'      => $user->passwd,
                'speed_limit' => $user->getRawOriginal('speed_limit'),
                'enable'      => $user->enable,
            ];
        }

        return $this->returnData('获取用户列表成功', 200, 'success', $data ?? [], ['updateTime' => time()]);
    }
}
