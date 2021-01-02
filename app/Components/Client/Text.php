<?php

namespace App\Components\Client;

class Text
{
    public static function buildShadowsocks($server)
    {
        return '服务器：'.$server['host'].PHP_EOL.'服务器端口：'.$server['port'].PHP_EOL.'密码：'.$server['passwd'].PHP_EOL.'加密：'.$server['method'].PHP_EOL;
    }

    public static function buildShadowsocksr($server)
    {
        return '服务器：'.$server['host'].PHP_EOL.'服务器端口：'.$server['port'].PHP_EOL.'密码：'.$server['passwd'].PHP_EOL.'加密：'.$server['method'].PHP_EOL.'协议：'.$server['protocol'].PHP_EOL.'协议参数：'.$server['protocol_param'].PHP_EOL.'混淆：'.$server['obfs'].PHP_EOL.'混淆参数：'.$server['obfs_param'].PHP_EOL.'UDP：'.$server['udp'].PHP_EOL;
    }

    public static function buildVmess($server)
    {
        return '服务器：'.$server['host'].PHP_EOL.'端口：'.$server['port'].PHP_EOL.'加密方式：'.$server['method'].PHP_EOL.'用户ID：'.$server['uuid'].PHP_EOL.'额外ID：'.$server['v2_alter_id'].PHP_EOL.'传输协议：'.$server['v2_net'].PHP_EOL.'伪装类型：'.$server['v2_type'].PHP_EOL.'伪装域名：'.$server['v2_host'].PHP_EOL.'路径：'.$server['v2_path'].PHP_EOL.'TLS：'.$server['v2_tls'].PHP_EOL.'UDP：'.$server['udp'].PHP_EOL;
    }

    public static function buildTrojan($server)
    {
        return '服务器：'.$server['host'].PHP_EOL.'端口：'.$server['port'].PHP_EOL.'密码：'.$server['passwd'].PHP_EOL.'SNI：'.$server['sni'].PHP_EOL.'UDP：'.$server['udp'].PHP_EOL;
    }
}
