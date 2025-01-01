<?php

namespace App\Utils\Clients\Protocols;

use App\Utils\Library\Templates\Protocol;

class URLSchemes implements Protocol
{
    public static function build(array $servers, bool $isEncode = true): string
    {
        $validTypes = ['shadowsocks', 'shadowsocksr', 'vmess', 'trojan'];
        $url = '';

        foreach ($servers as $server) {
            if (in_array($server['type'], $validTypes, true)) {
                $url .= call_user_func([self::class, 'build'.ucfirst($server['type'])], $server);
            }
        }

        return $isEncode ? base64_encode($url) : $url;
    }

    public static function buildShadowsocks(array $server): string
    { // https://shadowsocks.org/doc/sip002.html
        $name = rawurlencode($server['name']);
        $str = base64url_encode("{$server['method']}:{$server['passwd']}");

        return "ss://$str@{$server['host']}:{$server['port']}#$name".PHP_EOL;
    }

    public static function buildShadowsocksr(array $server): string
    {
        $setting = "{$server['host']}:{$server['port']}:{$server['protocol']}:{$server['method']}:{$server['obfs']}:";

        return 'ssr://'.base64url_encode($setting.base64url_encode($server['passwd']).'/?'.http_build_query([
            'obfsparam' => base64url_encode($server['obfs_param'] ?? ''),
            'protoparam' => base64url_encode($server['protocol_param'] ?? ''),
            'remarks' => base64url_encode($server['name']),
            'group' => base64url_encode($server['group'] ?? ''),
            'udpport' => $server['udp'] ?? 0,
            'uot' => 0,
        ])).PHP_EOL;
    }

    public static function buildVmess(array $server): string
    {
        $config = [
            'v' => '2',
            'ps' => $server['name'],
            'add' => $server['host'],
            'port' => $server['port'],
            'id' => $server['uuid'],
            'aid' => $server['v2_alter_id'],
            'net' => $server['v2_net'] ?? '',
            'type' => $server['v2_type'] ?? '',
            'host' => $server['v2_host'] ?? '',
            'path' => $server['v2_path'] ?? '',
            'tls' => $server['v2_tls'] ?? '',
            'sni' => $server['v2_sni'] ?? '',
            'remark' => $server['name'],
        ];

        return 'vmess://'.base64_encode(json_encode($config)).PHP_EOL;
    }

    public static function buildTrojan(array $server): string
    {
        $name = rawurlencode($server['name']);
        $query = '';
        if (array_key_exists('sni', $server)) {
            $query = "?sni={$server['sni']}";
        }

        return "trojan://{$server['passwd']}@{$server['host']}:{$server['port']}$query#$name".PHP_EOL;
    }
}
