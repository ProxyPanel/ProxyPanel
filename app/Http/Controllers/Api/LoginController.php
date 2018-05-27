<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Models\User;
use App\Http\Models\UserSubscribe;
use Illuminate\Http\Request;
use Response;
use Cache;

/**
 * 登录接口
 * Class LoginController
 *
 * @package App\Http\Controllers
 */
class LoginController extends Controller
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    // 登录返回订阅信息
    public function login(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');
        $cacheKey = 'request_times_' . md5($request->getClientIp());

        // 10分钟内请求失败15次，则封IP一小时
        if (Cache::has($cacheKey)) {
            if (Cache::get($cacheKey) >= 15) {
                return Response::json(['status' => 'fail', 'data' => [], 'message' => '频繁访问失败，禁止访问1小时']);
            }
        } else {
            Cache::put($cacheKey, 1, 10);
        }

        if (!$username || !$password) {
            Cache::increment($cacheKey);

            return Response::json(['status' => 'fail', 'data' => [], 'message' => '账号密码不能为空']);
        }

        $user = User::query()->where('username', trim($username))->where('password', md5($password))->where('status', '>=', 0)->first();
        if (!$user) {
            Cache::increment($cacheKey);

            return Response::json(['status' => 'fail', 'data' => [], 'message' => '账号不存在或已被禁用']);
        }

        // 如果生成过订阅链接则生成一个
        $subscribe = UserSubscribe::query()->where('user_id', $user->id)->first();
        if (!$subscribe) {
            $code = $this->makeSubscribeCode();

            $obj = new UserSubscribe();
            $obj->user_id = $user->id;
            $obj->code = $code;
            $obj->times = 0;
            $obj->save();
        } else {
            $code = $subscribe->code;
        }

        // 用户信息
        unset($user->password, $user->remember_token);
        $data['user'] = $user;

        // 订阅链接
        $data['link'] = self::$config['subscribe_domain'] ? self::$config['subscribe_domain'] . '/s/' . $code : self::$config['website_url'] . '/s/' . $code;

        return Response::json(['status' => 'success', 'data' => $data, 'message' => '登录成功']);
    }
}