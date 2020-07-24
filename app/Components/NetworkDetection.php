<?php

namespace App\Components;

use GuzzleHttp\Client;
use Log;

class NetworkDetection {
	/**
	 * 用api.50network.com进行节点阻断检测
	 *
	 * @param  string   $ip    被检测的IP
	 * @param  boolean  $type  TRUE 为ICMP,FALSE 为tcp
	 * @param  int      $port  检测端口，默认为空
	 *
	 * @return bool|string
	 */
	public static function networkCheck($ip, $type, $port = null) {
		$url = 'https://api.50network.com/china-firewall/check/ip/'.($type? 'icmp/' : ($port? 'tcp_port/' : 'tcp_ack/')).$ip.($port? '/'.$port : '');
		$checkName = $type? 'ICMP' : 'TCP';
		$client = new Client(['timeout' => 10]);
		$request = $client->get($url);
		$result = json_decode($request->getBody(), true);

		if($request->getStatusCode() == 200){
			if(!$result){
				Log::warning("【".$checkName."阻断检测】检测".$ip."时，接口返回异常访问链接：".$url);

				return false;
			}

			if(!$result['success']){
				if($result['error'] === "execute timeout (3s)"){
					sleep(10);

					return self::networkCheck($ip, $type, $port);
				}

				Log::warning("【".$checkName."阻断检测】检测".$ip.($port?: '')."时，返回".var_export($result, true));
				return false;
			}

			if($result['firewall-enable'] && $result['firewall-disable']){
				return "通讯正常"; // 正常
			}

			if($result['firewall-enable'] && !$result['firewall-disable']){
				return "海外阻断"; // 国外访问异常
			}

			if(!$result['firewall-enable'] && $result['firewall-disable']){
				return "国内阻断"; // 被墙
			}

			return "机器宕机"; // 服务器宕机
		}
		return false;
	}

	/**
	 * 用外部API进行Ping检测
	 *
	 * @param  string  $ip  被检测的IP或者域名
	 *
	 * @return bool|array
	 */
	public static function ping($ip) {
		$url = 'https://api.oioweb.cn/api/hostping.php?host='.$ip;//https://api.iiwl.cc/api/ping.php?host=
		$client = new Client(['timeout' => 20]);
		$request = $client->get($url);
		$message = json_decode($client->get($url)->getBody(), true);

		// 发送成功
		if($request->getStatusCode() == 200){
			if($message && $message['code']){
				return $message['data'];
			}
			// 发送失败
			Log::warning("【PING】检测".$ip."时，返回".var_export($message, true));
			return false;
		}
		Log::warning("【PING】检测".$ip."时，接口返回异常访问链接：".$url);
		// 发送错误
		return false;
	}
}