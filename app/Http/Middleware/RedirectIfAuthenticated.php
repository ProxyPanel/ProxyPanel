<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Auth;
use Closure;

class RedirectIfAuthenticated {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure                  $next
	 * @param  string|null               $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = null) {
		if(Auth::guard($guard)->check()){
			return redirect(RouteServiceProvider::HOME);
		}

		return $next($request);
	}
}
