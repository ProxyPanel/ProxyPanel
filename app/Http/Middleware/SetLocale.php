<?php

namespace App\Http\Middleware;

use Agent;
use Closure;
use Illuminate\Http\Request;

use function app;

class SetLocale
{
    /**
     * 变更语言
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->has('locale')) {
            $lang = $request->session()->get('locale');
        } elseif ($request->query('locale')) {
            $lang = $request->query('locale');
        } elseif (Agent::languages()) {
            $langs = array_keys(config('common.language'));
            $langs_low = array_map('strtolower', $langs);
            $accept = array_map('strtolower', str_replace('-', '_', Agent::languages()));
            $intersects = array_intersect($accept, $langs_low);

            if ($intersects) {
                $lang = array_values($langs)[array_search(array_values($intersects)[0], $langs_low, true)];
            }
        }

        if (isset($lang) && $lang !== app()->getLocale()) {
            app()->setLocale($lang);
            session()->put('locale', $lang);
        }

        return $next($request);
    }
}
