<?php

namespace App\Http\Middleware;

use App\Components\Helpers;
use App\Components\QQWry;
use App\Http\Models\Config;
use Response;
use Agent;
use Log;
use Closure;

class Forbidden
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 拒绝机器人访问
        $config = Config::query()->where('name', 'is_forbid_robot')->first();
        if ($config && $config->value) {
            if (Agent::isRobot()) {
                Log::info("识别到机器人访问(" . getClientIp() . ")");

                return Response::view('error.403', [], 403);
            }
        }

        $isIPv6 = false;
        $ip = getClientIP();
        $qqwry = new QQWry();
        $ipInfo = $qqwry->ip($ip);
        if (isset($ipInfo['error'])) {
            $isIPv6 = true;
            Log::info('无法识别IP，可能是IPv6，尝试解析：' . $ip);
            $ipInfo = getIPv6($ip);
        }

        // 拒绝无IP请求
        if (empty($ipInfo) || empty($ipInfo['country'])) {
            return Response::view('error.403', [], 403);
        }

        if (!in_array($ipInfo['country'], ['本机地址', '局域网'])) {
            // 拒绝大陆IP访问
            if (Helpers::systemConfig()['is_forbid_china']) {
                if (($ipInfo['country'] == '中国' && !in_array($ipInfo['province'], ['香港', '澳门', '台湾'])) || ($isIPv6 && $ipInfo['country'] == 'China')) {
                    Log::info('识别到大陆IP，拒绝访问：' . $ip);

                    return Response::view('error.403', [], 403);
                }
            }

            // 拒绝非大陆IP访问
            if (Helpers::systemConfig()['is_forbid_oversea']) {
                if ($ipInfo['country'] != '中国' || in_array($ipInfo['province'], ['香港', '澳门', '台湾']) || ($isIPv6 && $ipInfo['country'] != 'China')) {
                    Log::info('识别到海外IP，拒绝访问：' . $ip . ' - ' . $ipInfo['country']);

                    return Response::view('error.403', [], 403);
                }
            }
        }

        return $next($request);
    }
}
