<?php

namespace App\Http\Middleware;

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
     * @param  \Closure $next
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


        return $next($request);
    }
}
