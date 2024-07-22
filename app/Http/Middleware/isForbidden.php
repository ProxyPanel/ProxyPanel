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
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // 拒绝机器人访问
        if (sysConfig('is_forbid_robot') && Agent::isRobot()) {
            Log::warning(trans('errors.forbidden.bots').': '.IP::getClientIp());

            return Response::view('auth.error', ['message' => trans('errors.forbidden.bots')], 403);
        }

        // 拒绝通过非网站链接访问网站，防止网站被探测
        if ($this->isForbiddenSubscription($request)) {
            Log::warning(trans('errors.forbidden.redirect', ['ip' => IP::getClientIp(), 'url' => $request->fullUrl()]));

            return redirect(sysConfig('redirect_url', 'https://www.baidu.com').'?url='.$request->url());
        }

        // 拒绝特定IP访问
        if (sysConfig('forbid_mode') && $this->isForbiddenIP()) {
            return Response::view('auth.error', ['message' => trans('errors.forbidden.access')], 403);
        }

        return $next($request);
    }

    /**
     * 检查是否通过非网站链接访问.
     */
    private function isForbiddenSubscription(Request $request): bool
    {
        return config('app.env') === 'production' && sysConfig('website_url') && ! str_contains(sysConfig('website_url'), $request->getHost());
    }

    /**
     * 检查是否为禁止访问的IP.
     */
    private function isForbiddenIP(): bool
    {
        $ip = IP::getClientIP();
        $ipLocation = IP::getIPInfo($ip);

        if (! $ipLocation || empty(array_filter($ipLocation))) {
            Log::warning(trans('errors.forbidden.access').": $ip");

            return true;
        }

        if (! in_array($ipLocation['country'], ['本机地址', '局域网'])) {
            switch (sysConfig('forbid_mode')) {
                case 'ban_mainland':
                    if (in_array($ipLocation['country'], ['China', '中国', 'CN']) &&
                        ! in_array($ipLocation['region'], ['Taiwan', 'Hong Kong', 'Macao', '香港', '澳门', '台湾', '台湾省'])) {
                        Log::warning(trans('errors.forbidden.china').": $ip");

                        return true;
                    }
                    break;
                case 'ban_china':
                    if (in_array($ipLocation['country'], ['China', '中国', 'Taiwan', 'Hong Kong', 'Macao', '香港', '台湾', '澳门'])) {
                        Log::warning(trans('errors.forbidden.china').": $ip");

                        return true;
                    }
                    break;
                case 'ban_oversea':
                    if (! in_array($ipLocation['country'], ['China', '中国', 'Taiwan', 'Hong Kong', 'Macao', '香港', '台湾', '澳门'])) {
                        Log::warning(trans('errors.forbidden.oversea').": $ip - ".$ipLocation['country']);

                        return true;
                    }
                    break;
                default:
                    Log::emergency(trans('errors.forbidden.unknown'));

                    return true;
            }
        }

        return false;
    }
}
