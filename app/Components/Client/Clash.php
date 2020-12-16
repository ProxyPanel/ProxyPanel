<?php

namespace App\Components\Client;

/*
 * 本文件依据Clash文件编辑
 * https://github.com/Dreamacro/clash/tree/master/adapters/outbound
 *
 */

class Clash
{
    public static function buildShadowsocks($server)
    {
        return [
            'name' => $server['name'],
            'type' => 'ss',
            'server' => $server['host'],
            'port' => $server['port'],
            'password' => $server['passwd'],
            'cipher' => $server['method'],
            'udp' => $server['udp'],
        ];
    }

    public static function buildShadowsocksr($server)
    {
        return [
            'name' => $server['name'],
            'type' => 'ssr',
            'server' => $server['host'],
            'port' => $server['port'],
            'password' => $server['passwd'],
            'cipher' => $server['method'],
            'obfs' => $server['obfs'],
            'obfsparam' => $server['obfs_param'],
            'protocol' => $server['protocol'],
            'protocolparam' => $server['protocol_param'],
            'udp' => $server['udp'],
        ];
    }

    public static function buildVmess($server)
    {
        $array = [
            'name' => $server['name'],
            'type' => 'vmess',
            'server' => $server['host'],
            'port' => $server['port'],
            'uuid' => $server['uuid'],
            'alterId' => $server['v2_alter_id'],
            'cipher' => $server['method'],
            'udp' => $server['udp'],
        ];

        if ($server['v2_tls']) {
            $array['tls'] = true;
            $array['servername'] = $server['v2_host'];
        }
        $array['network'] = $server['v2_net'];

        if ($server['v2_net'] === 'ws') {
            $array['ws-path'] = $server['v2_path'];
            $array['ws-headers'] = ['Host' => $server['v2_host']];
        }

        return $array;
    }

    public static function buildTrojan($server)
    {
        $array = [
            'name' => $server['name'],
            'type' => 'trojan',
            'server' => $server['host'],
            'port' => $server['port'],
            'password' => $server['passwd'],
            'udp' => $server['udp'],
        ];

        if (! empty($server['sni'])) {
            $array['sni'] = $server['sni'];
        }

        return $array;
    }
}
