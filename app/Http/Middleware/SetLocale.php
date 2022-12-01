<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
        } elseif ($request->query('locale')) {
            Session::put('locale', $request->query('locale'));
            app()->setLocale($request->query('locale'));
        } elseif ($request->header('content-language')) {
            Session::put('locale', $request->header('content-language'));
            App::setLocale($request->header('content-language'));
        }

        return $next($request);
    }
}
