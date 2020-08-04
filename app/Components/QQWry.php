<?php

namespace App\Components;

use itbdw\Ip\IpLocation;

class QQWry
{
    /**
     * 查询IP地址的详细信息
     *
     * @param string $ip IPv4
     *
     * @return array
     */
    public static function ip($ip)
    {
        $filePath = public_path('qqwry.dat');

        return IpLocation::getLocation($ip, $filePath);
    }
}