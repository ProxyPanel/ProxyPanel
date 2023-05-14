<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class isMaintenance
{
    /**
     * 校验是否开启维护模式.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (sysConfig('maintenance_mode')) {
            return response()->view('auth.maintenance', [
                'message' => sysConfig('maintenance_content'),
                'time' => sysConfig('maintenance_time') ?: '0',
            ]);
        }

        return $next($request);
    }
}
