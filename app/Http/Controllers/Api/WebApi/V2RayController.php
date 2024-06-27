<?php

namespace App\Http\Controllers\Api\WebApi;

use App\Helpers\WebApiResponse;
use App\Models\Node;
use App\Models\NodeCertificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class V2RayController extends Controller
{
    use WebApiResponse;

    public function getNodeInfo(Node $node): JsonResponse // 获取节点信息
    {
        $cert = NodeCertificate::whereDomain($node->profile['v2_host'])->first();
        $tlsProvider = ! empty($node->profile['tls_provider']) ? $node->profile['tls_provider'] : sysConfig('v2ray_tls_provider');

        return $this->succeed([
            'id'              => $node->id,
            'is_udp'          => (bool) $node->is_udp,
            'speed_limit'     => $node->getRawOriginal('speed_limit'),
            'client_limit'    => $node->client_limit,
            'push_port'       => $node->push_port,
            'redirect_url'    => (string) sysConfig('redirect_url', ''),
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

    public function getUserList(Node $node): JsonResponse // 获取节点可用的用户列表
    {
        foreach ($node->users() as $user) {
            $data[] = [
                'uid'         => $user->id,
                'vmess_uid'   => $user->vmess_id,
                'speed_limit' => $user->getRawOriginal('speed_limit'),
            ];
        }

        return $this->succeed($data ?? [], ['updateTime' => time()]);
    }

    public function addCertificate(Node $node): JsonResponse // 上报节点伪装域名证书信息
    {
        if (request()->has(['key', 'pem'])) {
            $cert = NodeCertificate::whereDomain($node->v2_host)->firstOrCreate(['domain' => $node->server]);
            if ($cert && $cert->update(['key' => request('key'), 'pem' => request('pem')])) {
                return $this->succeed();
            }
        }

        return $this->failed([400201, '上报节点伪装域名证书失败，请检查字段']);
    }
}
