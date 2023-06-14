<?php

namespace App\Http\Middleware;

use App\Utils\IP;
use Cache;
use Closure;
use Log;
use Response;

class isSecurity
{
    /**
     * 是否需要安全码才访问(仅用于登录页).
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ip = IP::getClientIP();
        $code = $request->securityCode;
        $cacheKey = 'SecurityLogin_'.ip2long($ip);
        $websiteSecurityCode = sysConfig('website_security_code');

        if ($websiteSecurityCode && ! Cache::has($cacheKey)) {
            if ($code !== $websiteSecurityCode) {
                Log::warning(trans('errors.unsafe_enter').$ip);

                return Response::view('auth.safe');
            }

            Cache::put($cacheKey, $ip, 7200); // 2小时之内无需再次输入安全码访问
        }

        return $next($request);
    }
}
