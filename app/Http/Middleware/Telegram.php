<?php

namespace App\Http\Middleware;

use Closure;

class Telegram
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (sysConfig('telegram_token') && hash_equals(sysConfig('telegram_token'), $request->input('access_token'))) {
            abort(500, 'authentication failed');
        }

        return $next($request);
    }
}
