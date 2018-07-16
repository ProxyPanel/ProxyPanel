<?php

namespace App\Http\Middleware;

use Closure;
use Redirect;
use App\Http\Models\User as U;

class User
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
        if (!$request->session()->has('user')) {
            if ($request->cookie("remember")) {
                $u = U::query()->where("remember_token", $request->cookie("remember"))->first();
                if ($u) {
                    $request->session()->put('user', $u->toArray());

                    return $next($request);
                }
            }

            return Redirect::to('login');
        }

        return $next($request);
    }
}
