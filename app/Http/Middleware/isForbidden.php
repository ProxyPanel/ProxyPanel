<?php

namespace App\Http\Middleware;

use Agent;
use App\Components\IP;
use Closure;
use Illuminate\Http\Request;
use Log;
use Response;

class isForbidden
{
    /**
     * 限制机器人、指定IP访问.
     *
     * @param  Request  $request
     * @param  Closure  $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 拒绝机器人访问
        if (sysConfig('is_forbid_robot') && Agent::isRobot()) {
            Log::info('识别到机器人访问('.IP::getClientIp().')');

            return Response::view('auth.error', ['message' => trans('error.ForbiddenRobot')], 403);
        }

        // 拒绝通过订阅链接域名访问网站，防止网站被探测
        if (false !== strpos(sysConfig('subscribe_domain'), $request->getHost())
            && ! str_contains(sysConfig('subscribe_domain'), sysConfig('website_url'))) {
            Log::info('识别到通过订阅链接访问，强制跳转至百度('.IP::getClientIp().')');

            return redirect('https://www.baidu.com');
        }

        $ip = IP::getClientIP();
        $ipLocation = IP::getIPInfo($ip);

        // 拒绝无IP请求
        if (! $ipLocation || empty(array_filter($ipLocation))) {
            return Response::view('auth.error', ['message' => trans('error.ForbiddenAccess')], 403);
        }

        if (! in_array($ipLocation['country'], ['本机地址', '局域网']) && sysConfig('forbid_mode')) {
            // 拒绝大陆IP访问
            switch (sysConfig('forbid_mode')) {
                case 'ban_mainland':
                    if (in_array($ipLocation['country'], ['China', '中国']) && ! in_array($ipLocation['province'], ['香港', '澳门', '台湾', '台湾省'])) {
                        Log::info('识别到大陆IP，拒绝访问：'.$ip);

                        return Response::view('auth.error', ['message' => trans('error.ForbiddenChina')], 403);
                    }
                    break;
                case 'ban_china':
                    if (in_array($ipLocation['country'], ['China', '中国', 'Taiwan', 'Hong Kong', 'Macao'])) {
                        Log::info('识别到中国IP，拒绝访问：'.$ip);

                        return Response::view('auth.error', ['message' => trans('error.ForbiddenChina')], 403);
                    }
                    break;
                case 'ban_oversea':
                    if (! in_array($ipLocation['country'], ['China', '中国', 'Taiwan', 'Hong Kong', 'Macao'])) {
                        Log::info('识别到海外IP，拒绝访问：'.$ip.' - '.$ipLocation['country']);

                        return Response::view('auth.error', ['message' => trans('error.ForbiddenOversea')], 403);
                    }
                    break;
                default:
                    Log::error('未知禁止访问模式！请在系统设置中修改【禁止访问模式】！');
                    break;
            }
        }

        return $next($request);
    }
}
