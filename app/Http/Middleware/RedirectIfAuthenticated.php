<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Auth;
use Closure;
use Illuminate\Http\Request;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param Request     $request
     * @param Closure     $next
     * @param string|null $guard
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
