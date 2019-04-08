<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Redirect;

class isAdmin
{
    /**
     * 校验是否为管理员身份
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::user()->is_admin) {
            return Redirect::to('/');
        }

        return $next($request);
    }
}
