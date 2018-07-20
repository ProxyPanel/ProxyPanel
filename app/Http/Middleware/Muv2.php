<?php

namespace App\Http\Middleware;

use App\Http\Models\SsNode;
use Closure;
use Redirect;

class Muv2
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 验证mukey
        $muKey = $request->header("Token", '');
        if ($muKey != $_ENV['MU_KEY']) { // TODO:改造成每个节点都有一个mukey
            return response()->json([
                'ret' => 0,
                'msg' => 'token or source is invalid'
            ], 401);
        }

        // 验证IP是否在节点IP列表当中
        $node = SsNode::query()->where('ip', $_SERVER["REMOTE_ADDR"])->orWhere('ipv6', $_SERVER["REMOTE_ADDR"])->first();
        if (!$node && $_SERVER["REMOTE_ADDR"] != '127.0.0.1') {
            return response()->json([
                'ret' => 0,
                'msg' => 'token or source is invalid'
            ], 401);
        }

        return $next($request);
    }

}