<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseEnum;
use App\Helpers\WebApiResponse;
use Closure;

class WebApi
{
    /**
     * Handle an incoming request.
     *
     * @param  $request
     * @param  Closure  $next
     * @return mixed
     */
    use WebApiResponse;

    public function handle($request, Closure $next)
    {
        $node = $request->node;
        $key = $request->header('key');
        $time = $request->header('timestamp');

        if (! isset($key)) { // 未提供 key
            return $this->failed([400200, 'Your key is null!']);
        }

        $nodeAuth = $node->auth ?? null;
        if (! isset($nodeAuth) || $key !== $nodeAuth->key) { // key不存在/不匹配
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR);
        }

        if (abs($time - time()) >= 300) { // 时差超过5分钟
            return $this->failed(ResponseEnum::CLIENT_HTTP_UNSYNCHRONIZE_TIMER);
        }

        $request->route()->forgetParameter('prefix');

        return $next($request);
    }
}
