<?php

namespace App\Http\Middleware;

use App\Http\Models\SsNode;
use Closure;
use Response;
use Redirect;

class Muv2
{
    public function handle($request, Closure $next)
    {
        // 验证MU_KEY
        $muKey = $request->header("Token", '');
        if ($muKey != $_ENV['MU_KEY']) {
            return Response::json([
                'ret' => 0,
                'msg' => 'Invalid Token.'
            ], 401);
        }

        // 验证IP是否在节点IP列表当中
        $ip = $request->getClientIp();
        $node = SsNode::query()->where('ip', $ip)->orWhere('ipv6', $ip)->first();
        if (!$node && $ip != '127.0.0.1') {
            return Response::json([
                'ret' => 0,
                'msg' => 'Invalid Token.'
            ], 401);
        }

        return $next($request);
    }

}