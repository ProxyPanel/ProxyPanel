<?php

namespace App\Http\Middleware;

use Closure;
use Redirect;
use Auth;

class Admin
{
    /**
     * Handle an incoming request.
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
