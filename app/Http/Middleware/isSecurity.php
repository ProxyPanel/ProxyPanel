<?php

namespace App\Http\Middleware;

use Cache;
use Closure;
use Log;
use Response;

class isSecurity
{

    /**
     * 是否需要安全码才访问(仅用于登录页)
     *
     * @param           $request
     * @param  Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ip                  = getClientIP();
        $code                = $request->securityCode;
        $cacheKey            = 'SecurityLogin_' . ip2long($ip);
        $websiteSecurityCode = sysConfig('website_security_code');

        if ($websiteSecurityCode && ! Cache::has($cacheKey)) {
            if ($code != $websiteSecurityCode) {
                Log::info("拒绝非安全入口访问(" . $ip . ")");

                return Response::view(
                    'auth.error',
                    [
                        'message' => trans('error.SecurityError') . ', 
                        ' . trans(
                                'error.Visit'
                            ) . '<a href="/login?securityCode=" target="_self">' .
                                     trans('error.SecurityEnter') . '</a>',
                    ],
                    403
                );
            }

            Cache::put($cacheKey, $ip, 7200); // 2小时之内无需再次输入安全码访问
        }

        return $next($request);
    }

}
