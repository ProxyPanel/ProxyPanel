<?php

namespace App\Http\Middleware;

use Closure;
use Redirect;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        return $next($request);
    }
}
