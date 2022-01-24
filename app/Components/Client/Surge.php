<?php

namespace App\Components\Client;

class Surge
{
    public static function buildShadowsocks($server)
    {
        $config = array_filter([
            "{$server['name']}=ss",
            $server['host'],
            $server['port'],
            "encrypt-method={$server['method']}",
            "password={$server['passwd']}",
            'tfo=true',
            "udp-relay={$server['udp']}",
        ]);

        return implode(',', $config).PHP_EOL;
    }

    public static function buildVmess($server)
    {
        $config = [
            "{$server['name']}=vmess",
            $server['host'],
            $server['port'],
            "username={$server['uuid']}",
            'tfo=true',
            "udp-relay={$server['udp']}",
        ];

        if ($server['v2_tls']) {
            array_push($config, 'tls=true', "sni={$server['v2_host']}");
        }
        if ($server['v2_net'] === 'ws') {
            array_push($config, 'ws=true', "ws-path={$server['v2_path']}", "ws-headers=Host:{$server['v2_host']}");
        }

        return implode(',', $config).PHP_EOL;
    }

    public static function buildTrojan($server)
    {
        $config = array_filter([
            "{$server['name']}=trojan",
            $server['host'],
            $server['port'],
            "password={$server['passwd']}",
            $server['sni'] ? "sni={$server['sni']}" : '',
            'tfo=true',
            "udp-relay={$server['udp']}",
        ]);

        return implode(',', $config).PHP_EOL;
    }
}
