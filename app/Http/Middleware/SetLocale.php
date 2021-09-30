<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;

class SetLocale
{
    /**
     * 变更语言
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
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
