<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Models\Node;
use App\Models\NodeCertificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class V2RayController extends BaseController
{
    // 获取节点信息
    public function getNodeInfo($id): JsonResponse
    {
        $node = Node::find($id);
        $nodeDv = NodeCertificate::whereDomain($node->v2_host)->first();
        $tlsProvider = $node->tls_provider ?: sysConfig('v2ray_tls_provider');
        if (! $tlsProvider) {
            $tlsProvider = null;
        }

        return $this->returnData('获取节点信息成功', 'success', 200, [
            'id' => $node->id,
            'is_udp' => $node->is_udp ? true : false,
            'speed_limit' => $node->getRawOriginal('speed_limit'),
            'client_limit' => $node->client_limit,
            'push_port' => $node->push_port,
            'redirect_url' => (string) sysConfig('redirect_url'),
            'secret' => $node->auth->secret,
            'key' => $nodeDv->key ?? '',
            'pem' => $nodeDv->pem ?? '',
            'v2_license' => (string) sysConfig('v2ray_license'),
            'v2_alter_id' => $node->v2_alter_id,
            'v2_port' => $node->v2_port,
            'v2_method' => $node->v2_method,
            'v2_net' => $node->v2_net,
            'v2_type' => $node->v2_type,
            'v2_host' => $node->v2_host,
            'v2_path' => $node->v2_path,
            'v2_tls' => $node->v2_tls ? true : false,
            'v2_tls_provider' => $tlsProvider,
        ]);
    }

    // 获取节点可用的用户列表
    public function getUserList($id): JsonResponse
    {
        $users = Node::find($id)->node_access_users;
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'uid' => $user->id,
                'vmess_uid' => $user->vmess_id,
                'speed_limit' => $user->getRawOriginal('speed_limit'),
            ];
        }

        return $this->returnData('获取用户列表成功', 'success', 200, $data, ['updateTime' => time()]);
    }

    // 上报节点伪装域名证书信息
    public function addCertificate(Request $request, $id): JsonResponse
    {
        $key = $request->input('key');
        $pem = $request->input('pem');

        if ($request->has(['key', 'pem'])) {
            $node = Node::find($id);
            $Dv = NodeCertificate::whereDomain($node->v2_host)->first();
            if ($Dv) {
                $ret = NodeCertificate::whereId($Dv->id)->update(['key' => $key, 'pem' => $pem]);
            } else {
                $ret = new NodeCertificate();
                $ret->domain = $node->server;
                $ret->key = $request->input('key');
                $ret->pem = $request->input('pem');
                $ret->save();
            }

            if ($ret) {
                return $this->returnData('上报节点伪装域名证书成功', 'success', 200);
            }
        }

        return $this->returnData('上报节点伪装域名证书失败，请检查字段');
    }
}
