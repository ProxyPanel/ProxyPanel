<?php

namespace App\Components\Client;

class QuantumultX
{
    public static function buildShadowsocks($server)
    {
        $config = [
            "shadowsocks={$server['host']}:{$server['port']}",
            "method={$server['method']}",
            "password={$server['passwd']}",
            'fast-open=true',
            "udp-relay={$server['udp']}",
            "tag={$server['name']}",
        ];
        $config = array_filter($config);

        return implode(',', $config)."\r\n";
    }

    public static function buildShadowsocksr($server)
    {
        $config = [
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
        ];
        $config = array_filter($config);

        return implode(',', $config)."\r\n";
    }

    public static function buildVmess($server)
    {
        $config = [
            "vmess={$server['host']}:{$server['port']}",
            "method={$server['method']}",
            "password={$server['uuid']}",
            'fast-open=true',
            'udp-relay=true',
            "tag={$server['name']}",
        ];

        if ($server['v2_tls']) {
            if ($server['v2_net'] === 'tcp') {
                $config[] = 'obfs=over-tls';
            } else {
                $config[] = 'obfs=wss';
            }
        } else {
            if ($server['v2_net'] === 'ws') {
                $config[] = 'obfs=ws';
            }
        }

        if ($server['v2_tls']) {
            $config[] = 'tls-verification=true';
            if (isset($server['v2_host']) && ! empty($server['v2_host'])) {
                $config[] = "tls-host={$server['v2_host']}";
            }
        }

        if ($server['v2_type'] === 'ws' && isset($server['v2_path']) && ! empty($server['v2_path'])) {
            $config[] = "obfs-uri={$server['v2_path']}";
            $config[] = "obfs-host={$server['v2_host']}";
        }

        return implode(',', $config)."\r\n";
    }

    public static function buildTrojan($server)
    {
        $config = [
            "trojan={$server['host']}:{$server['port']}",
            "password={$server['passwd']}",
            'over-tls=true',
            $server['host'] ? "tls-host={$server['host']}" : '',
            // Tips: allowInsecure=false = tls-verification=true
            // $server['allow_insecure'] ? 'tls-verification=false' : 'tls-verification=true',
            'fast-open=true',
            'udp-relay=true',
            "tag={$server['name']}",
        ];
        $config = array_filter($config);

        return implode(',', $config)."\r\n";
    }
}
