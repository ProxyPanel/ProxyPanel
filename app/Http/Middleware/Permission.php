<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (app('auth')->guard($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $route = request()->route()->getName();
        if (app('auth')->guard($guard)->user()->can($route)) {
            return $next($request);
        }

        throw UnauthorizedException::forPermissions((array) $route);
    }
}
