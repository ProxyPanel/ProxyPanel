<?php

namespace App\Http\Middleware;

use App\Components\Helpers;
use Closure;
use Log;
use Cache;

class isSecurity
{
    /**
     * 是否需要安全码才访问(仅用于登录页)
     *
     * @param         $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ip = getClientIP();
        $code = $request->get('securityCode');
        $cacheKey = 'SecurityLogin_' . ip2long($ip);
        $websiteSecurityCode = Helpers::systemConfig()['website_security_code'];

        if ($websiteSecurityCode && !Cache::has($cacheKey)) {
            if ($code != $websiteSecurityCode) {
                Log::info("拒绝非安全入口访问(" . $ip . ")");

                return response()->view('auth.error', ['message' => '请使用安全码从<a href="/login?securityCode=" target="_self">安全入口</a>访问']);
            } else {
                Cache::put($cacheKey, $ip, 120); // 缓存120分钟，因为每个session默认存活120分钟
            }
        }

        return $next($request);
    }
}
