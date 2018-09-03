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
        \Log::info(json_encode($request->header()));

        # v2ray客户端提交参数说明
        # https://github.com/catpie/musdk-go/blob/master/http.go
        # 传入参数：
        # Token:mu_key
        # ServiceType:5 ---> https://github.com/catpie/musdk-go/blob/master/ret.go
        # Content-Type:application/json

        //$serviceType = $request->header('ServiceType'); //
        //$agent = $request->header('user-agent'); // Go-http-client/1.1

        // 验证MU_KEY
        $token = $request->header("Token", '');
        if ($token != $_ENV['MU_KEY']) {
            return Response::json([
                'ret' => 0,
                'msg' => 'Invalid Token.'
            ], 401);
        }

        // 验证IP是否在节点IP列表当中
        $ip = getClientIp();
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