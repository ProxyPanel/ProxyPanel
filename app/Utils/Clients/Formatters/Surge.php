<?php

namespace App\Utils\Clients\Formatters;

use App\Utils\Library\Templates\Formatter;

class Surge implements Formatter
{
    // https://manual.nssurge.com/policy/proxy.html
    public static function build(array $servers): array
    {
        $validTypes = ['shadowsocks', 'vmess', 'trojan', 'hysteria2'];
        $names = '';
        $proxies = '';

        foreach ($servers as $server) {
            if (in_array($server['type'], $validTypes, true)) {
                $names .= $server['name'].', ';
                $proxies .= call_user_func([self::class, 'build'.ucfirst($server['type'])], $server);
            }
        }

        return ['name' => rtrim($names, ', '), 'proxies' => $proxies];
    }

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

        if (isset($server['v2_tls']) && $server['v2_tls']) {
            array_push($config, 'tls=true', "sni={$server['v2_host']}");
        }
        if (isset($server['v2_net']) && $server['v2_net'] === 'ws') {
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
            isset($server['sni']) ? "sni={$server['sni']}" : '',
            'tfo=true',
            "udp-relay={$server['udp']}",
            "skip-cert-verify={$server['allow_insecure']}",
        ]);

        return implode(',', $config).PHP_EOL;
    }

    public static function buildHysteria2(array $server): string
    {
        $config = array_filter([
            "{$server['name']}=hysteria2",
            $server['host'],
            $server['port'],
            "password={$server['passwd']}",
            isset($server['sni']) ? "sni={$server['sni']}" : '',
            "udp-relay={$server['udp']}",
            isset($server['ports']) && $server['ports'] ? 'port-hopping='.str_replace(',', ';', $server['ports']) : '',
            isset($server['allow_insecure']) && $server['allow_insecure'] ? 'skip-cert-verify=true' : '',
            //            isset($server['upload_mbps']) ? "upload-bandwidth={$server['upload_mbps']}" : '',
            //            isset($server['download_mbps']) ? "download-bandwidth={$server['download_mbps']}Mbps" : '',
        ]);

        return implode(',', $config).PHP_EOL;
    }

    public static function buildShadowsocksr(array $server): array|string
    {
        return '';
    }
}
