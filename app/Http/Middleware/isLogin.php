<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Redirect;

class isLogin
{
    /**
     * 校验是否已登录.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->guest()) {
            if ($request->routeIs('admin.*')) {
                return Redirect::route('admin.login');
            }

            return Redirect::route('login');
        }

        return $next($request);
    }
}
