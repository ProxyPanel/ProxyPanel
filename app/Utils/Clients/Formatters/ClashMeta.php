<?php

namespace App\Utils\Clients\Formatters;

use App\Utils\Library\Templates\Formatter;

class ClashMeta implements Formatter
{
    // https://wiki.metacubex.one/config/proxies/
    public static function build(array $servers): array
    {
        $validTypes = ['shadowsocks', 'shadowsocksr', 'vmess', 'trojan', 'hysteria2'];

        foreach ($servers as $server) {
            if (in_array($server['type'], $validTypes, true)) {
                $proxy = call_user_func([self::class, 'build'.ucfirst($server['type'])], $server);
                if ($proxy) {
                    $ids[] = $server['id'];
                    $proxies[] = $proxy;
                }
            }
        }

        return ['ids' => $ids ?? [], 'proxies' => $proxies ?? []];
    }

    public static function buildShadowsocks(array $server): ?array
    {
        $supportedMethods = ['aes-128-ctr', 'aes-192-ctr', 'aes-256-ctr', 'aes-128-cfb', 'aes-192-cfb', 'aes-256-cfb', 'aes-128-gcm', 'aes-192-gcm', 'aes-256-gcm', 'aes-128-ccm', 'aes-192-ccm', 'aes-256-ccm', 'aes-128-gcm-siv', 'aes-256-gcm-siv', 'chacha20-ietf', 'chacha20', 'xchacha20', 'chacha20-ietf-poly1305', 'xchacha20-ietf-poly1305', 'chacha8-ietf-poly1305', 'xchacha8-ietf-poly1305', '2022-blake3-aes-128-gcm', '2022-blake3-aes-256-gcm', '2022-blake3-chacha20-poly1305', 'lea-128-gcm', 'lea-192-gcm', 'lea-256-gcm', 'rabbit128-poly1305', 'aegis-128l', 'aegis-256', 'aez-384', 'deoxys-ii-256-128', 'rc4-md5', 'none'];

        if (! in_array($server['method'], $supportedMethods, true)) {
            return null;
        }

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

    public static function buildShadowsocksr(array $server): ?array
    {
        $supportedMethods = ['aes-128-ctr', 'aes-192-ctr', 'aes-256-ctr', 'aes-128-cfb', 'aes-192-cfb', 'aes-256-cfb', 'rc4-md5', 'chacha20', 'chacha20-ietf', 'none'];

        $supportedObfuscations = ['plain', 'http_simple', 'http_post', 'random_head', 'tls1.2_ticket_auth', 'tls1.2_ticket_fastauth'];

        $supportedProtocols = ['origin', 'auth_sha1_v4', 'auth_aes128_md5', 'auth_aes128_sha1', 'auth_chain_a', 'auth_chain_b'];

        if (! in_array($server['method'], $supportedMethods, true) || ! in_array($server['obfs'], $supportedObfuscations, true) || ! in_array($server['protocol'], $supportedProtocols, true)) {
            return null;
        }

        return [
            'name' => $server['name'],
            'type' => 'ssr',
            'server' => $server['host'],
            'port' => $server['port'],
            'cipher' => $server['method'],
            'password' => $server['passwd'],
            'obfs' => $server['obfs'],
            'protocol' => $server['protocol'],
            'obfs-param' => $server['obfs_param'] ?? '',
            'protocol-param' => $server['protocol_param'] ?? '',
            'udp' => $server['udp'],
        ];
    }

    public static function buildVmess(array $server): ?array
    {
        $supportedMethods = ['auto', 'none', 'zero', 'aes-128-gcm', 'chacha20-poly1305'];

        $supportedNetworks = ['ws', 'h2', 'http', 'grpc', 'tcp'];

        if (! in_array($server['method'], $supportedMethods, true) || ($server['v2_net'] && ! in_array($server['v2_net'], $supportedNetworks, true))) {
            return null;
        }

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

        if (isset($server['v2_tls']) && $server['v2_tls']) {
            $array['tls'] = true;
            $array['servername'] = $server['v2_host'];
        }
        $array['network'] = $server['v2_net'];

        if (isset($server['v2_net']) && $server['v2_net'] === 'ws') {
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

        if (isset($server['sni'])) {
            $array['sni'] = $server['sni'];
        }

        if (isset($server['allow_insecure'])) {
            $array['skip-cert-verify'] = $server['allow_insecure'];
        }

        return $array;
    }

    public static function buildHysteria2(array $server): array
    {
        $array = [
            'name' => $server['name'],
            'type' => 'hysteria2',
            'server' => $server['host'],
            'port' => $server['port'],
            'password' => $server['passwd'],
            'udp' => $server['udp'],
            'sni' => $server['host'],
        ];

        if (isset($server['ports']) && $server['ports']) {
            $array['ports'] = $server['ports'];
        }

        if (isset($server['obfs']) && $server['obfs']) {
            $array['obfs'] = $server['obfs'];
            $array['obfs-password'] = $server['obfs_param'];
        }

        if (isset($server['allow_insecure']) && $server['allow_insecure']) {
            $array['skip-cert-verify'] = $server['allow_insecure'];
        }

        return $array;
    }
}
