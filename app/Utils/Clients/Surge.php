<?php

namespace App\Utils\Clients;

use App\Utils\Library\Templates\Client;

class Surge implements Client
{
    public static function buildShadowsocks(array $server): string
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

    public static function buildVmess(array $server): string
    {
        $config = [
            "{$server['name']}=vmess",
            $server['host'],
            $server['port'],
            "username={$server['uuid']}",
            'vmess-aead=true',
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

    public static function buildTrojan(array $server): string
    {
        $config = array_filter([
            "{$server['name']}=trojan",
            $server['host'],
            $server['port'],
            "password={$server['passwd']}",
            $server['sni'] ? "sni={$server['sni']}" : '',
            'tfo=true',
            "udp-relay={$server['udp']}",
            // "skip-cert-verify={$server['allow_insecure']}"
        ]);

        return implode(',', $config).PHP_EOL;
    }

    public static function buildShadowsocksr(array $server): array|string
    {
        return '';
    }
}
