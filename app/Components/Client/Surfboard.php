<?php

namespace App\Components\Client;

class Surfboard
{
    public static function buildShadowsocks($server)
    {
        $config = [
            "{$server['name']}=custom",
            "{$server['host']}",
            "{$server['port']}",
            "{$server['method']}",
            "{$server['passwd']}",
            sysConfig('website_url').'/clients/SSEncrypt.module',
            'tfo=true',
            "udp-relay={$server['udp']}",
        ];
        $config = array_filter($config);

        return implode(',', $config).PHP_EOL;
    }

    public static function buildVmess($server)
    {
        $config = [
            "{$server['name']}=vmess",
            "{$server['host']}",
            "{$server['port']}",
            "username={$server['uuid']}",
            'tfo=true',
            "udp-relay={$server['udp']}",
        ];

        if ($server['v2_tls']) {
            $config[] = 'tls=true';
            $config[] = "sni={$server['v2_host']}";
        }
        if ($server['v2_net'] === 'ws') {
            $config[] = 'ws=true';
            $config[] = "ws-path={$server['v2_path']}";
            $config[] = "ws-headers=Host:{$server['v2_host']}";
        }

        return implode(',', $config).PHP_EOL;
    }
}
