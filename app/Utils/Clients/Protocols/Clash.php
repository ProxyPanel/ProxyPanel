<?php

namespace App\Utils\Clients\Protocols;

use App\Utils\Library\Templates\Protocol;

class Clash implements Protocol
{
    public static function build(array $servers): array
    {
        $validTypes = ['shadowsocks', 'shadowsocksr', 'vmess', 'trojan'];

        $names = [];
        $proxies = [];

        foreach ($servers as $server) {
            if (in_array($server['type'], $validTypes, true)) {
                $names[] = $server['name'];
                $proxies[] = call_user_func([self::class, 'build'.ucfirst($server['type'])], $server);
            }
        }

        return ['name' => $names, 'proxies' => $proxies];
    }

    public static function buildShadowsocks(array $server): array
    {
        return [
            'name' => $server['name'],
            'type' => 'ss',
            'server' => $server['host'],
            'port' => $server['port'],
            'cipher' => $server['method'],
            'password' => $server['passwd'],
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
