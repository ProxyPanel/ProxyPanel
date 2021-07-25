<?php

namespace App\Http\Middleware;

use Closure;

class Telegram
{
    /**
     * Handle an incoming request.
     *
     * @param           $request
     * @param  Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (hash_equals(sysConfig('telegram_token'), $request->input('access_token'))) {
            abort(500, 'authentication failed');
        }

        return $next($request);
    }
}
