<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Models\Node;
use App\Models\NodeCertificate;
use Illuminate\Http\JsonResponse;

class V2RayController extends CoreController
{
    // 获取节点信息
    public function getNodeInfo(Node $node): JsonResponse
    {
        $cert = NodeCertificate::whereDomain($node->profile['v2_host'])->first();
        $tlsProvider = ! empty($node->profile['tls_provider']) ? $node->profile['tls_provider'] : sysConfig('v2ray_tls_provider');
        if (! $tlsProvider) {
            $tlsProvider = null;
        }

        return $this->returnData('获取节点信息成功', 200, 'success', [
            'id'              => $node->id,
            'is_udp'          => (bool) $node->is_udp,
            'speed_limit'     => $node->getRawOriginal('speed_limit'),
            'client_limit'    => $node->client_limit,
            'push_port'       => $node->push_port,
            'redirect_url'    => (string) sysConfig('redirect_url'),
            'secret'          => $node->auth->secret,
            'key'             => $cert ? $cert->key : '',
            'pem'             => $cert ? $cert->pem : '',
            'v2_license'      => (string) sysConfig('v2ray_license'),
            'v2_alter_id'     => (int) $node->profile['v2_alter_id'],
            'v2_port'         => $node->port,
            'v2_method'       => $node->profile['method'] ?? '',
            'v2_net'          => $node->profile['v2_net'] ?? '',
            'v2_type'         => $node->profile['v2_type'] ?? '',
            'v2_host'         => $node->profile['v2_host'] ?? '',
            'v2_path'         => $node->profile['v2_path'] ?? '',
            'v2_tls'          => (bool) ($node->profile['v2_tls'] ?? false),
            'v2_tls_provider' => $tlsProvider,
        ]);
    }

    // 获取节点可用的用户列表
    public function getUserList(Node $node): JsonResponse
    {
        foreach ($node->users() as $user) {
            $data[] = [
                'uid'         => $user->id,
                'vmess_uid'   => $user->vmess_id,
                'speed_limit' => $user->getRawOriginal('speed_limit'),
            ];
        }

        return $this->returnData('获取用户列表成功', 200, 'success', $data ?? [], ['updateTime' => time()]);
    }

    // 上报节点伪装域名证书信息
    public function addCertificate(Node $node): JsonResponse
    {
        if (request()->has(['key', 'pem'])) {
            $cert = NodeCertificate::whereDomain($node->v2_host)->firstOrCreate(['domain' => $node->server]);
            if ($cert && $cert->update(['key' => request('key'), 'pem' => request('pem')])) {
                return $this->returnData('上报节点伪装域名证书成功', 200, 'success');
            }
        }

        return $this->returnData('上报节点伪装域名证书失败，请检查字段');
    }
}
