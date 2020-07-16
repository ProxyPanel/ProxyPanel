<?php

namespace App\Components;

use Exception;
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

		try{
			$ret = json_decode(Curl::send($url), true);
			if(!$ret){
				Log::warning("【".$checkName."阻断检测】检测".$ip."时，接口返回异常访问链接：".$url);

				return false;
			}

			if(!$ret['success']){
				if($ret['error'] === "execute timeout (3s)"){
					sleep(10);

					return self::networkCheck($ip, $type, $port);
				}

				Log::warning("【".$checkName."阻断检测】检测".$ip.($port?: '')."时，返回".json_encode($ret));
				return false;
			}
		}catch(Exception $e){
			Log::warning("【".$checkName."阻断检测】检测".$ip."时，接口请求超时".$e);

			return false;
		}

		if($ret['firewall-enable'] && $ret['firewall-disable']){
			return "通讯正常"; // 正常
		}

		if($ret['firewall-enable'] && !$ret['firewall-disable']){
			return "海外阻断"; // 国外访问异常
		}

		if(!$ret['firewall-enable'] && $ret['firewall-disable']){
			return "国内阻断"; // 被墙
		}

		return "机器宕机"; // 服务器宕机
	}

	/**
	 * 用api.iiwl.cc进行Ping检测
	 *
	 * @param  string  $ip  被检测的IP或者域名
	 *
	 * @return bool|array
	 */
	public static function ping($ip) {
		$url = 'https://api.iiwl.cc/api/ping.php?url='.$ip;

		try{
			$ret = json_decode(Curl::send($url), true);
			if(!$ret){
				Log::warning("【PING】检测".$ip."时，接口返回异常访问链接：".$url);

				return false;
			}

			if($ret['code'] != 1 || $ret['msg'] !== "检测成功！"){
				Log::warning("【PING】检测".$ip."时，返回".json_encode($ret));

				return false;
			}
		}catch(Exception $e){
			Log::warning("【Ping】检测".$ip."时，接口请求超时".$e);

			return false;
		}

		return $ret['data']; // 服务器宕机
	}
}