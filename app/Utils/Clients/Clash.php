<?php

namespace App\Utils\Clients;

/*
 * 本文件依据
 * https://github.com/Dreamacro/clash/tree/master/adapter/outbound
 * https://github.com/Dreamacro/clash/wiki/Configuration#all-configuration-options
 * https://lancellc.gitbook.io/clash/clash-config-file/proxies/config-a-shadowsocks-proxy
 *
 */

use App\Utils\Library\Templates\Client;

class Clash implements Client
{
    public static function buildShadowsocks(array $server): array
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

    public static function buildShadowsocksr(array $server): array
    {
        return [
            'name' => $server['name'],
            'type' => 'ssr',
            'server' => $server['host'],
            'port' => $server['port'],
            'password' => $server['passwd'],
            'cipher' => $server['method'],
            'obfs' => $server['obfs'],
            'obfs-param' => $server['obfs_param'],
            'protocol' => $server['protocol'],
            'protocol-param' => $server['protocol_param'],
            'udp' => $server['udp'],
        ];
    }

    public static function buildVmess(array $server): array
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
            $array['ws-opts'] = [];
            $array['ws-opts']['path'] = $server['v2_path'];
            if ($server['v2_host']) {
                $array['ws-opts']['headers'] = ['Host' => $server['v2_host']];
            }
            $array['ws-path'] = $server['v2_path'];
            if ($server['v2_host']) {
                $array['ws-headers'] = ['Host' => $server['v2_host']];
            }
        }

        return $array;
    }

    public static function buildTrojan(array $server): array
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
