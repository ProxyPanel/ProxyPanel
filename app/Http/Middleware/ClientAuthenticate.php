<?php

namespace App\Http\Middleware;

use App\Helpers\ClientApiResponse;
use App\Helpers\ResponseEnum;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ClientAuthenticate
{
    use ClientApiResponse;

    /**
     * 校验客户端身份：优先 Bearer token，其次回落到客户端登录时写进 session 的 uid。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 面板 web 会话不是客户端凭据：sanctum guard 默认含 web，只在带 Bearer token 时才用它
        $user = $request->bearerToken() ? $request->user('sanctum') : null;
        $uid = $request->session()->get('uid');
        $user = $user ?: ($uid ? User::find($uid) : null);

        if (! $user || $user->status === -1) {
            // 走 failed()：jsonResponse() 用真值判 status，传负数会被判成 success
            return $this->failed(ResponseEnum::USER_SERVICE_LOGIN_ERROR);
        }

        // 绑定到默认 guard，控制器里 auth()->user() 取的就是这一个用户
        auth()->setUser($user);

        return $next($request);
    }
}
