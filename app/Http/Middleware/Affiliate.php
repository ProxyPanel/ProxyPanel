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
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $aff = $request->input('aff');
        if ($aff) {
            Cookie::queue('register_aff', $aff, 129600);
        }

        return $next($request);
    }
}
