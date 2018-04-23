<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Illuminate\Support\Facades\App;

class SetLocale
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
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        if ($request->query('locale')) {
            Session::put('locale', $request->query('locale'));
            App::setLocale($request->query('locale'));
        }

        return $next($request);
    }

}