<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Helpers\WebApiResponse;
use App\Models\Node;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class TrojanController extends Controller
{
    use WebApiResponse;

    public function getNodeInfo(Node $node): JsonResponse // 获取节点信息
    {
        return $this->succeed([
            'id' => $node->id,
            'is_udp' => (bool) $node->is_udp,
            'speed_limit' => $node->getRawOriginal('speed_limit'),
            'client_limit' => $node->client_limit,
            'push_port' => $node->push_port,
            'redirect_url' => sysConfig('redirect_url'),
            'trojan_port' => $node->port,
            'secret' => $node->auth->secret,
            'license' => sysConfig('trojan_license'),
        ]);
    }

    public function getUserList(Node $node): JsonResponse // 获取节点可用的用户列表
    {
        foreach ($node->users() as $user) {
            $data[] = [
                'uid' => $user->id,
                'password' => $user->passwd,
                'speed_limit' => $user->getRawOriginal('speed_limit'),
            ];
        }

        return $this->succeed($data ?? [], ['updateTime' => time()]);
    }
}
