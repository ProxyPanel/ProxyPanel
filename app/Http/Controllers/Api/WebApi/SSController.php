<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Helpers\WebApiResponse;
use App\Models\Node;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class SSController extends Controller
{
    use WebApiResponse;

    public function getNodeInfo(Node $node): JsonResponse // 获取节点信息
    {
        $data = [
            'id' => $node->id,
            'method' => $node->profile['method'] ?? '',
            'speed_limit' => $node->getRawOriginal('speed_limit'),
            'client_limit' => $node->client_limit,
            'redirect_url' => sysConfig('redirect_url'),
        ];

        if (! empty($node->profile['passwd'])) {
            $data['port'] = $node->port;
        }

        return $this->succeed($data);
    }

    public function getUserList(Node $node): JsonResponse // 获取节点可用的用户列表
    {
        foreach ($node->users() as $user) {
            $data[] = [
                'uid' => $user->id,
                'port' => $user->port,
                'passwd' => $user->passwd,
                'speed_limit' => $user->getRawOriginal('speed_limit'),
                'enable' => $user->enable,
            ];
        }

        return $this->succeed($data ?? [], ['updateTime' => time()]);
    }
}
