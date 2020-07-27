<?php

namespace App\Components;

use ipip\db\City;

class IPIP {
	/**
	 * 查询IP地址的详细信息
	 *
	 * @param  string  $ip  IPv4
	 *
	 * @return array|null
	 */
	public static function ip($ip): ?array {
		$filePath = database_path('ipip.ipdb');
		return (new City($filePath))->findMap($ip, 'CN');
	}
}
