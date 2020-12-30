<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Response;

class WebApi
{
    /**
     * Handle an incoming request.
     *
     * @param           $request
     * @param  Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $node = $request->node;
        $key = $request->header('key');
        $time = $request->header('timestamp');

        if (! isset($key)) {// 未提供 key
            return $this->returnData('Your key is null!');
        }

        $nodeAuth = $node->auth ?? null;
        if (! isset($nodeAuth) || $key !== $nodeAuth->key) {// key不存在/不匹配
            return $this->returnData('Token is invalid!');
        }

        if (abs($time - time()) >= 300) {// 时差超过5分钟
            return $this->returnData('Please resynchronize the server time!');
        }

        return $next($request);
    }

    // 返回数据
    public function returnData(string $message): JsonResponse
    {
        return Response::json(['status' => 'fail', 'code' => 404, 'message' => $message]);
    }
}
