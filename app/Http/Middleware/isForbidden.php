<?php

namespace App\Http\Middleware;

use Agent;
use App\Components\Helpers;
use App\Components\IPIP;
use App\Components\QQWry;
use Closure;
use Illuminate\Http\Request;
use Log;

class isForbidden {
	/**
	 * 限制机器人、指定IP访问
	 *
	 * @param  Request  $request
	 * @param  Closure  $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		// 拒绝机器人访问
		if(Helpers::systemConfig()['is_forbid_robot'] && Agent::isRobot()){
			Log::info("识别到机器人访问(".getClientIp().")");

			return response()->view('auth.error', ['message' => trans('error.ForbiddenRobot')], 404);
		}

		// 拒绝通过订阅链接域名访问网站，防止网站被探测
		if(true === strpos(Helpers::systemConfig()['subscribe_domain'], $request->getHost())
		   && !str_contains(Helpers::systemConfig()['subscribe_domain'], Helpers::systemConfig()['website_url'])){
			Log::info("识别到通过订阅链接访问，强制跳转至百度(".getClientIp().")");

			return redirect('https://www.baidu.com');
		}

		$ip = getClientIP();
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
			Log::info('识别到IPv6，尝试解析：'.$ip);
			$isIPv6 = true;
			$ipInfo = getIPInfo($ip);
		}else{
			$isIPv6 = false;
			$ipInfo = QQWry::ip($ip); // 通过纯真IP库解析IPv4信息
			if(isset($ipInfo['error'])){
				Log::info('无法识别IPv4，尝试使用IPIP的IP库解析：'.$ip);
				$ipip = IPIP::ip($ip);
				$ipInfo = [
					'country'  => $ipip['country_name'],
					'province' => $ipip['region_name'],
					'city'     => $ipip['city_name']
				];
			}else{
				// 判断纯真IP库获取的国家信息是否与IPIP的IP库获取的信息一致，不一致则用IPIP的（因为纯真IP库的非大陆IP准确率较低）
				$ipip = IPIP::ip($ip);
				if($ipInfo['country'] != $ipip['country_name']){
					$ipInfo['country'] = $ipip['country_name'];
					$ipInfo['province'] = $ipip['region_name'];
					$ipInfo['city'] = $ipip['city_name'];
				}
			}
		}

		// 拒绝无IP请求
		if(empty($ipInfo) || empty($ipInfo['country'])){
			return response()->view('auth.error', ['message' => trans('error.ForbiddenAccess')], 403);
		}

		if(!in_array($ipInfo['country'], ['本机地址', '局域网'])){
			// 拒绝大陆IP访问
			if(Helpers::systemConfig()['is_forbid_china']){
				if(($isIPv6 && $ipInfo['country'] === 'China')
				   || ($ipInfo['country'] === '中国'
				       && !in_array($ipInfo['province'], ['香港', '澳门', '台湾']))){
					Log::info('识别到大陆IP，拒绝访问：'.$ip);

					return response()->view('auth.error', ['message' => trans('error.ForbiddenChina')], 403);
				}
			}

			// 拒绝非大陆IP访问
			if(Helpers::systemConfig()['is_forbid_oversea']){
				if(($isIPv6 && $ipInfo['country'] !== 'China') || $ipInfo['country'] !== '中国'
				   || in_array($ipInfo['province'], ['香港', '澳门', '台湾'])){
					Log::info('识别到海外IP，拒绝访问：'.$ip.' - '.$ipInfo['country']);

					return response()->view('auth.error', ['message' => trans('error.ForbiddenOversea')], 403);
				}
			}
		}

		return $next($request);
	}
}
