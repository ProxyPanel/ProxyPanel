<?php

namespace App\Components;

use GuzzleHttp\Client;

class QQInfo {
	public static function getName(string $qq): string {
		//向接口发起请求获取json数据
		$url = 'https://r.qzone.qq.com/fcg-bin/cgi_get_portrait.fcg?get_nick=1&uins='.$qq;
		$client = new Client(['timeout' => 10]);
		$request = $client->get($url);
		$message = mb_convert_encoding($request->getBody(), "UTF-8", "GBK");

		// 接口是否异常
		if($request->getStatusCode() == 200){
			if(strpos($message, $qq) !== false){
				//对获取的json数据进行截取并解析成数组
				$message = json_decode(substr($message, 17, -1), true);

				return stripslashes($message[$qq][6]);
			}
		}

		return $qq;
	}

	public static function getName2(string $qq): string {
		//向接口发起请求获取json数据
		$url = 'https://api.toubiec.cn/qq?qq='.$qq.'&size=100';
		$client = new Client(['timeout' => 10]);
		$request = $client->get($url);
		$message = json_decode($request->getBody(), true);

		// 接口是否异常
		if($request->getStatusCode() == 200 && $message && $message['code'] == 200){
			return $message['name'];
		}

		return $qq;
	}

	public static function getName3(string $qq): string {
		//向接口发起请求获取json数据
		$url = 'https://api.unipay.qq.com/v1/r/1450000186/wechat_query?cmd=1&pf=mds_storeopen_qb-__mds_qqclub_tab_-html5&pfkey=pfkey&from_h5=1&from_https=1&openid=openid&openkey=openkey&session_id=hy_gameid&session_type=st_dummy&qq_appid=&offerId=1450000186&sandbox=&provide_uin='.$qq;
		$client = new Client(['timeout' => 10]);
		$request = $client->get($url);
		$message = json_decode($request->getBody(), true);

		// 接口是否异常
		if($request->getStatusCode() == 200 && $message && $message['ret'] == 0){
			return urldecode($message['nick']);
		}

		return $qq;
	}
}
