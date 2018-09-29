<?php

namespace App\Http\Middleware;

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
                Log::info("识别到机器人访问(" . $request->getClientIp() . ")");

                return Response::view('403', [], 403);
            }
        }

        // 拒绝受限IP访问
        $ip = getClientIP();
        $qqwry = new QQWry();
        $ipInfo = $qqwry->ip($ip);
        if (is_array($ipInfo) && $ipInfo['country'] != '本机地址' && $ipInfo['country'] != '局域网') {
            $forbidChina = Config::query()->where('name', 'is_forbid_china')->first();
            if ($forbidChina && $forbidChina->value && $ipInfo['country'] == '中国') {
                Log::info('识别到大陆IP，拒绝访问：' . $ip);

                return Response::view('403', [], 403);
            }

            $forbidOversea = Config::query()->where('name', 'is_forbid_oversea')->first();
            if ($forbidOversea && $forbidOversea->value && $ipInfo['country'] != '中国') {
                Log::info('识别到海外IP，拒绝访问：' . $ip . ' - ' . $ipInfo['country']);

                return Response::view('403', [], 403);
            }
        }

        return $next($request);
    }
}
