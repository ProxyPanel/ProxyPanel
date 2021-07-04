<?php

namespace App\Components\Client;

class URLSchemes
{
    public static function buildShadowsocks($server)
    {
        $name = rawurlencode($server['name']);
        $str = base64url_encode("{$server['method']}:{$server['method']}");

        return "ss://{$str}@{$server['host']}:{$server['port']}#{$name}".PHP_EOL;
    }

    public static function buildShadowsocksr($server)
    {
        $setting = "{$server['host']}:{$server['port']}:{$server['protocol']}:{$server['method']}:{$server['obfs']}:";

        return 'ssr://'.base64url_encode($setting.base64url_encode($server['passwd']).'/?obfsparam='.base64url_encode($server['obfs_param']).'&protoparam='.base64url_encode($server['protocol_param']).'&remarks='.base64url_encode($server['name']).'&group='.base64url_encode($server['group']).'&udpport='.$server['udp'].'&uot=0').PHP_EOL;
    }

    // TODO: More study required about id usage https://shadowsocks.org/en/wiki/SIP008-Online-Configuration-Delivery.html
    public static function buildShadowsocksSIP008($server)
    {
        return [
            'id'          => $server['id'],
            'remark'      => $server['name'],
            'server'      => $server['host'],
            'server_port' => $server['port'],
            'password'    => $server['passwd'],
            'method'      => $server['method'],
        ];
    }

    public static function buildVmess($server)
    {
        $config = [
            'v'    => '2',
            'ps'   => $server['name'],
            'add'  => $server['host'],
            'port' => $server['port'],
            'id'   => $server['uuid'],
            'aid'  => $server['v2_alter_id'],
            'net'  => $server['v2_net'],
            'type' => $server['v2_type'],
            'host' => $server['v2_host'],
            'path' => $server['v2_path'],
            'tls'  => $server['v2_tls'],
        ];

        return 'vmess://'.base64_encode(json_encode($config)).PHP_EOL;
    }

    public static function buildTrojan($server)
    {
        $name = rawurlencode($server['name']);
        $query = '';
        if (array_key_exists('relay_server', $server)) {
            $query = "?sni={$server['relay_server']}";
        }

        return "trojan://{$server['passwd']}@{$server['host']}:{$server['port']}{$query}#{$name}".PHP_EOL;
    }
}
