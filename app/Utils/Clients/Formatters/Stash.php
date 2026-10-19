<?php

namespace App\Utils\Clients\Formatters;

use App\Utils\Library\Templates\Formatter;

class Stash implements Formatter
{
    use TypeDispatch;

    // https://stash.wiki/proxy-protocols/proxy-types
    public static function types(): array
    {
        return ['shadowsocks', 'shadowsocksr', 'vmess', 'trojan', 'hysteria2'];
    }

    public static function build(array $servers): array
    {
        return self::assembleProxies($servers);
    }

    public static function buildShadowsocks(array $server): ?array
    {
        $supportedMethods = ['aes-128-gcm', 'aes-192-gcm', 'aes-256-gcm', 'aes-128-cfb', 'aes-192-cfb', 'aes-256-cfb', 'aes-128-ctr', 'aes-192-ctr', 'aes-256-ctr', 'rc4-md5', 'chacha20', 'chacha20-ietf', 'xchacha20', 'chacha20-ietf-poly1305', 'xchacha20-ietf-poly1305', '2022-blake3-aes-128-gcm', '2022-blake3-aes-256-gcm'];

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
        $supportedMethods = ['aes-128-gcm', 'aes-192-gcm', 'aes-256-gcm', 'aes-128-cfb', 'aes-192-cfb', 'aes-256-cfb', 'aes-128-ctr', 'aes-192-ctr', 'aes-256-ctr', 'rc4-md5', 'chacha20', 'chacha20-ietf', 'xchacha20', 'chacha20-ietf-poly1305', 'xchacha20-ietf-poly1305', '2022-blake3-aes-128-gcm', '2022-blake3-aes-256-gcm', 'none'];

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
            'obfs-param' => $server['obfs_param'] ?? '',
            'protocol' => $server['protocol'],
            'protocol-param' => $server['protocol_param'] ?? '',
        ];
    }

    public static function buildVmess(array $server): ?array
    {
        $supportedMethods = ['auto', 'aes-128-gcm', 'chacha20-poly1305', 'none'];

        $supportedNetworks = ['ws', 'h2', 'http', 'grpc'];

        if (! in_array($server['method'], $supportedMethods, true) || ($server['v2_net'] && ! in_array($server['v2_net'], $supportedNetworks, true))) {
            return null;
        }

        $array = [
            'name' => $server['name'],
            'type' => 'vmess',
            'server' => $server['host'],
            'port' => $server['port'],
            'uuid' => $server['uuid'],
            'cipher' => $server['method'],
            'alterId' => $server['v2_alter_id'],
            'udp' => $server['udp'] ?? false,
        ];

        if (isset($server['v2_tls']) && $server['v2_tls']) {
            $array['tls'] = true;
            $array['servername'] = $server['v2_host'] ?? '';
        }
        $array['network'] = $server['v2_net'] ?? 'tcp';

        if (isset($server['v2_net']) && $server['v2_net'] === 'ws') {
            $array['ws-opts'] = [];
            $array['ws-opts']['path'] = $server['v2_path'] ?? '/';
            if (! empty($server['v2_host'])) {
                $array['ws-opts']['headers'] = ['Host' => $server['v2_host']];
            }
            $array['ws-path'] = $server['v2_path'] ?? '/';
            if (! empty($server['v2_host'])) {
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

        if (isset($server['allow_insecure'])) {
            $array['skip-cert-verify'] = $server['allow_insecure'];
        }

        return $array;
    }

    public static function buildHysteria2(array $server): ?array
    {
        if (isset($server['obfs'])) {
            return null;
        }

        $array = [
            'name' => $server['name'],
            'type' => 'hysteria2',
            'server' => $server['host'],
            'port' => $server['port'],
            'auth' => $server['passwd'],
            'fast-open' => true,
            'udp' => $server['udp'] ?? true,
            'sni' => $server['host'],
        ];

        if (isset($server['ports'])) {
            $array['ports'] = $server['ports'];
        }

        if (isset($server['allow_insecure'])) {
            $array['skip-cert-verify'] = $server['allow_insecure'];
        }

        return $array;
    }
}
