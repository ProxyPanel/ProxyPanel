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
    public function ip($ip)
    {
        $filePath = storage_path('qqwry.dat');

        return IpLocation::getLocation($ip, $filePath);
    }
}