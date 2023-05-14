<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Helpers\WebApiResponse;
use App\Models\Node;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class SSRController extends Controller
{
    use WebApiResponse;

    public function getNodeInfo(Node $node): JsonResponse // 获取节点信息
    {
        return $this->succeed($node->getSSRConfig());
    }

    // 获取节点可用的用户列表
    public function getUserList(Node $node): JsonResponse
    {
        foreach ($node->users() as $user) {
            $data[] = [
                'uid' => $user->id,
                'port' => $user->port,
                'passwd' => $user->passwd,
                'method' => $user->method,
                'protocol' => $user->protocol,
                'obfs' => $user->obfs,
                'obfs_param' => $node->profile['obfs_param'] ?? '',
                'speed_limit' => $user->getRawOriginal('speed_limit'),
                'enable' => $user->enable,
            ];
        }

        return $this->succeed($data ?? [], ['updateTime' => time()]);
    }
}
