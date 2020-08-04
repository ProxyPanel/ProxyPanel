<?php

namespace App\Http\Middleware;

use Closure;
use Session;

class SetLocale
{
    /**
     * 变更语言
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session::has('locale')) {
            app()->setLocale(Session::get('locale'));
        }

        if ($request->query('locale')) {
            Session::put('locale', $request->query('locale'));
            app()->setLocale($request->query('locale'));
        }

        return $next($request);
    }

}