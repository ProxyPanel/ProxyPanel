<?php

namespace App\Components;

use GuzzleHttp\Client;
use Log;
use LSS\XML2Array;

class Namesilo {
	protected static $host;
	protected static $systemConfig;

	// Todo Debug测试
	public function __construct() {
		self::$host = 'https://www.namesilo.com/api/';
		self::$systemConfig = Helpers::systemConfig();
	}

	// 列出账号下所有域名
	public function listDomains() {
		return $this->send('listDomains');
	}

	// 发送请求
	private function send($operation, $data = []) {
		$params = [
			'version' => 1,
			'type'    => 'xml',
			'key'     => self::$systemConfig['namesilo_key']
		];
		$query = array_merge($params, $data);

		$content = '请求操作：['.$operation.'] --- 请求数据：['.http_build_query($query).']';

		$client = new Client(['timeout' => 10]);
		$request = $client->get(self::$host.$operation.'?'.http_build_query($query));
		$result = XML2Array::createArray(json_decode($request->getBody(), true));

		if($request->getStatusCode() != 200){
			Log::error('请求失败：'.var_export($request, true));
			Helpers::addNotificationLog('[Namesilo API] - ['.$operation.']', $content, 1,
				self::$systemConfig['webmaster_email'], 0, var_export($request, true));

			return false;
		}

		// 出错
		if(empty($result['namesilo']) || $result['namesilo']['reply']['code'] != 300 || $result['namesilo']['reply']['detail'] !== 'success'){
			Helpers::addNotificationLog('[Namesilo API] - ['.$operation.']', $content, 1,
				self::$systemConfig['webmaster_email'], 0, $result['namesilo']['reply']['detail']);
		}else{
			Helpers::addNotificationLog('[Namesilo API] - ['.$operation.']', $content, 1,
				self::$systemConfig['webmaster_email'], 1, $result['namesilo']['reply']['detail']);
		}

		return $result['namesilo']['reply'];
	}

	// 列出指定域名的所有DNS记录
	public function dnsListRecords($domain) {
		$query = [
			'domain' => $domain
		];

		return $this->send('dnsListRecords', $query);
	}

	// 为指定域名添加DNS记录
	public function dnsAddRecord($domain, $host, $value, $type = 'A', $ttl = 7207) {
		$query = [
			'domain'  => $domain,
			'rrtype'  => $type,
			'rrhost'  => $host,
			'rrvalue' => $value,
			'rrttl'   => $ttl
		];

		return $this->send('dnsAddRecord', $query);
	}

	// 更新DNS记录
	public function dnsUpdateRecord($domain, $id, $host, $value, $ttl = 7207) {
		$query = [
			'domain'  => $domain,
			'rrid'    => $id,
			'rrhost'  => $host,
			'rrvalue' => $value,
			'rrttl'   => $ttl
		];

		return $this->send('dnsUpdateRecord', $query);
	}

	// 删除DNS记录
	public function dnsDeleteRecord($domain, $id) {
		$data = [
			'domain' => $domain,
			'rrid'   => $id
		];

		return $this->send('dnsDeleteRecord', $data);
	}
}
