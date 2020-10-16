<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;
use Illuminate\Http\Request;

class Affiliate
{
    /**
     * 返利识别.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $aff = $request->input('aff', 0);
        if ($aff) {
            Cookie::queue('register_aff', $aff, 129600);
        }

        return $next($request);
    }
}
