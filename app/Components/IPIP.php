<?php

namespace App\Components;

use ipip\db\City;

class IPIP
{
    /**
     * 查询IP地址的详细信息
     *
     * @param string $ip IPv4
     *
     * @return array|null
     */
    public static function ip($ip)
    {
        $filePath = public_path('ipip.ipdb');

        $loc = new City($filePath);
        $result = $loc->findMap($ip, 'CN');

        return $result;
    }
}