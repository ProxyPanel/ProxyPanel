<?php

namespace App\Http\Middleware;

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
	public function handle($request, Closure $next, $guard = NULL)
	{
		if(auth()->guard($guard)->check()){
			return redirect('/');
		}

		return $next($request);
	}
}
