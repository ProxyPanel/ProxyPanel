<?php

namespace App\Http\Middleware;

use Agent;
use App\Utils\IP;
use Closure;
use Illuminate\Http\Request;
use Log;
use Response;

class isForbidden
{
    /**
     * 限制机器人、指定IP访问.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    { // 限制机器人、指定IP访问.
        // 拒绝机器人访问
        if (sysConfig('is_forbid_robot') && Agent::isRobot()) {
            Log::warning('识别到机器人('.IP::getClientIp().')访问');

            return Response::view('auth.error', ['message' => trans('errors.forbidden.bots')], 403);
        }

        // 拒绝通过订阅链接域名访问网站，防止网站被探测
        if (config('app.env') === 'production' && sysConfig('website_url') && ! str_contains(sysConfig('website_url'), $request->getHost())) {
            Log::warning('识别到通过订阅链接访问，强制跳转至百度('.IP::getClientIp().') '.$request->fullUrl());

            return redirect('https://www.baidu.com');
        }

        if (sysConfig('forbid_mode')) {
            $ip = IP::getClientIP();
            $ipLocation = IP::getIPInfo($ip);

            if ($ipLocation !== false) {
                // 拒绝无IP请求
                if (empty($ipLocation) || empty(array_filter($ipLocation))) {
                    Log::warning("无法识别到IP，拒绝访问：$ip");

                    return Response::view('auth.error', ['message' => trans('errors.forbidden.access')], 403);
                }

                if (! in_array($ipLocation['country'], ['本机地址', '局域网'])) {
                    // 拒绝大陆IP访问
                    switch (sysConfig('forbid_mode')) {
                        case 'ban_mainland':
                            if (in_array($ipLocation['country'], ['China', '中国', 'CN']) && ! in_array($ipLocation['region'], ['Taiwan', 'Hong Kong', 'Macao', '香港', '澳门', '台湾', '台湾省'])) {
                                Log::warning("识别到大陆IP，拒绝访问：$ip");

                                return Response::view('auth.error', ['message' => trans('errors.forbidden.china')], 403);
                            }
                            break;
                        case 'ban_china':
                            if (in_array($ipLocation['country'], ['China', '中国', 'Taiwan', 'Hong Kong', 'Macao', '香港', '台湾', '澳门'])) {
                                Log::warning("识别到中国IP，拒绝访问：$ip");

                                return Response::view('auth.error', ['message' => trans('errors.forbidden.china')], 403);
                            }
                            break;
                        case 'ban_oversea':
                            if (! in_array($ipLocation['country'], ['China', '中国', 'Taiwan', 'Hong Kong', 'Macao', '香港', '台湾', '澳门'])) {
                                Log::warning("识别到海外IP，拒绝访问：$ip - ".$ipLocation['country']);

                                return Response::view('auth.error', ['message' => trans('errors.forbidden.oversea')], 403);
                            }
                            break;
                        default:
                            Log::emergency('未知禁止访问模式！请在系统设置中修改【禁止访问模式】！');
                            break;
                    }
                }
            }
        }

        return $next($request);
    }
}
