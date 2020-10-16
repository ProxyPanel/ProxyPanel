<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Redirect;

class isAdmin
{
    /**
     * 校验是否为管理员身份.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::getUser()->is_admin) {
            return Redirect::route('home');
        }

        return $next($request);
    }
}
