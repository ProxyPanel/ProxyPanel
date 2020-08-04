<?php

namespace App\Http\Middleware;

use Cookie;
use Closure;

class Affiliate
{
    /**
     * 返利识别
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $aff = trim($request->input('aff', 0));
        if ($aff) {
            Cookie::queue('register_aff', $aff, 129600);
        }

        return $next($request);
    }
}
