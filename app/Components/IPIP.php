<?php

namespace App\Components;

use ipip\db\Reader;

class IPIP
{
    /**
     * 查询IP地址的详细信息
     *
     * @param string $ip IPv4
     *
     * @return \ipip\db\Info|null
     * @throws \Exception
     */
    public function ip($ip)
    {
        $filePath = storage_path('ipip.ipdb');

        $db = new Reader($filePath);
        //$loc = $db->find($ip);
        //$loc = $db->findMap($ip);
        $loc = $db->findInfo($ip);

        return $loc;
    }
}