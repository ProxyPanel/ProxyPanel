<?php

namespace App\Utils\Clients;

/*
 * 本文件依据
 * https://github.com/crossutility/Quantumult-X/blob/master/server-complete.snippet
 *
 */

use App\Utils\Library\Templates\Client;

class QuantumultX implements Client
{
    public static function buildShadowsocks(array $server): string
    {
        $config = array_filter([
            "shadowsocks={$server['host']}:{$server['port']}",
            "method={$server['method']}",
            "password={$server['passwd']}",
            'fast-open=true',
            "udp-relay={$server['udp']}",
            "tag={$server['name']}",
        ]);

        return implode(',', $config).PHP_EOL;
    }

    public static function buildShadowsocksr(array $server): string
    {
        $config = array_filter([
            "shadowsocks={$server['host']}:{$server['port']}",
            "method={$server['method']}",
            "password={$server['passwd']}",
            "ssr-protocol={$server['protocol']}",
            "ssr-protocol-param={$server['protocol_param']}",
            "obfs={$server['obfs']}",
            "obfs-host={$server['obfs_param']}",
            'fast-open=true',
            "udp-relay={$server['udp']}",
            "tag={$server['name']}",
        ]);

        return implode(',', $config).PHP_EOL;
    }

    public static function buildVmess(array $server): string
    {
        $config = [
            "vmess={$server['host']}:{$server['port']}",
            "method={$server['method']}",
            "password={$server['uuid']}",
            'fast-open=true',
            "udp-relay={$server['udp']}",
            "tag={$server['name']}",
        ];

        if ($server['v2_tls']) {
            if ($server['v2_net'] === 'tcp') {
                $config[] = 'obfs=over-tls';
            } else {
                $config[] = 'obfs=wss';
            }
        } elseif ($server['v2_net'] === 'ws') {
            $config[] = 'obfs=ws';
        }

        if ($server['v2_tls']) {
            $config[] = 'tls-verification=true';
            if (! empty($server['v2_host'])) {
                $config[] = "tls-host={$server['v2_host']}";
            }
        }

        if ($server['v2_type'] === 'ws' && ! empty($server['v2_path'])) {
            $config[] = "obfs-uri={$server['v2_path']}";
            $config[] = "obfs-host={$server['v2_host']}";
        }

        return implode(',', $config).PHP_EOL;
    }

    public static function buildTrojan(array $server): string
    {
        $config = array_filter([
            "trojan={$server['host']}:{$server['port']}",
            "password={$server['passwd']}",
            'over-tls=true',
            $server['host'] ? "tls-host={$server['host']}" : '',
            // Tips: allowInsecure=false = tls-verification=true
            // $server['allow_insecure'] ? 'tls-verification=false' : 'tls-verification=true',
            'fast-open=true',
            "udp-relay={$server['udp']}",
            "tag={$server['name']}",
        ]);

        return implode(',', $config).PHP_EOL;
    }
}
