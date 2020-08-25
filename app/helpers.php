<?php

use App\Components\Helpers;
use GuzzleHttp\Client;

define('KB', 1024);
define('MB', 1048576);
define('GB', 1073741824);
define('TB', 1099511627776);
define('PB', 1125899906842624);

define('Minute', 60);
define('Hour', 3600);
define('Day', 86400);

define('Mbps', 125000);

// base64加密（处理URL）
if(!function_exists('base64url_encode')){
	function base64url_encode($data) {
		return strtr(base64_encode($data), ['+' => '-', '/' => '_', '=' => '']);
	}
}

// base64解密（处理URL）
if(!function_exists('base64url_decode')){
	function base64url_decode($data) {
		return base64_decode(strtr($data, '-_', '+/'));
	}
}

// 根据流量值自动转换单位输出
if(!function_exists('flowAutoShow')){
	function flowAutoShow($value) {
		$value = abs($value);
		if($value >= PB){
			return round($value / PB, 2)."PB";
		}

		if($value >= TB){
			return round($value / TB, 2)."TB";
		}

		if($value >= GB){
			return round($value / GB, 2)."GB";
		}

		if($value >= MB){
			return round($value / MB, 2)."MB";
		}

		if($value >= KB){
			return round($value / KB, 2)."KB";
		}

		return round($value, 2)."B";
	}
}

if(!function_exists('toMB')){
	function toMB($traffic) {
		return $traffic * MB;
	}
}

if(!function_exists('toGB')){
	function toGB($traffic) {
		return $traffic * GB;
	}
}

if(!function_exists('flowToGB')){
	function flowToGB($traffic) {
		return $traffic / GB;
	}
}

// 文件大小转换
if(!function_exists('formatBytes')){
	function formatBytes($bytes, $precision = 2) {
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];
		$bytes = max($bytes, 0);
		$pow = floor(($bytes? log($bytes) : 0) / log(KB));
		$pow = min($pow, count($units) - 1);
		$bytes /= KB ** $pow;

		return round($bytes, $precision).' '.$units[$pow];
	}
}

// 秒转时间
if(!function_exists('seconds2time')){
	function seconds2time($seconds) {
		$day = floor($seconds / Day);
		$hour = floor(($seconds % Day) / Hour);
		$minute = floor((($seconds % Day) % Hour) / Minute);
		if($day > 0){
			return $day.'天'.$hour.'小时'.$minute.'分';
		}

		if($hour != 0){
			return $hour.'小时'.$minute.'分';
		}

		return $minute.'分';
	}
}

// 获取访客真实IP
if(!function_exists('getClientIP')){
	function getClientIP() {
		/*
		 * 访问时用localhost访问的，读出来的是“::1”是正常情况
		 * ::1说明开启了IPv6支持，这是IPv6下的本地回环地址的表示
		 * 使用IPv4地址访问或者关闭IPv6支持都可以不显示这个
		 */
		if(isset($_SERVER)){
			if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])){
				$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
				$ip = $_SERVER['REMOTE_ADDR'];
			}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}elseif(isset($_SERVER['REMOTE_ADDR'])){
				$ip = $_SERVER['REMOTE_ADDR'];
			}else{
				$ip = 'unknown';
			}
		}elseif(getenv('HTTP_X_FORWARDED_FOR')){
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}elseif(getenv('HTTP_CLIENT_IP')){
			$ip = getenv('HTTP_CLIENT_IP');
		}else{
			$ip = getenv('REMOTE_ADDR');
		}

		if(trim($ip) === '::1'){
			$ip = '127.0.0.1';
		}

		return $ip;
	}
}

// 获取IPv6信息
if(!function_exists('getIPInfo')){
	function getIPInfo($ip) {
		$request = (new Client(['timeout' => 15]))->get('https://api.ip.sb/geoip/'.$ip);
		$message = json_decode($request->getBody(), true);

		if($request->getStatusCode() == 200){
			return $message;
		}

		Log::error('解析IPv6异常：'.$ip.PHP_EOL.var_export($request, true));
		return false;
	}
}

// 过滤emoji表情
if(!function_exists('filterEmoji')){
	function filterEmoji($str) {
		$str = preg_replace_callback('/./u', static function(array $match) {
			return strlen($match[0]) >= 4? '' : $match[0];
		}, $str);

		return $str;
	}
}

// 获取系统设置
if(!function_exists('sysConfig')){
	function sysConfig($name) {
		return Helpers::sysConfig()[$name];
	}
}