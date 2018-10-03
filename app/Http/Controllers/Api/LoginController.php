<?php

namespace App\Http\Controllers\Api;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Models\User;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use Illuminate\Http\Request;
use Response;
use Cache;
use DB;

/**
 * 登录接口
 * Class LoginController
 *
 * @package App\Http\Controllers
 */
class LoginController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    // 登录返回订阅信息
    public function login(Request $request)
    {
        $username = trim($request->get('username'));
        $password = trim($request->get('password'));
        $cacheKey = 'request_times_' . md5(getClientIp());

        // 连续请求失败10次，则封IP一小时
        if (Cache::has($cacheKey)) {
            if (Cache::get($cacheKey) >= 15) {
                return Response::json(['status' => 'fail', 'data' => [], 'message' => '请求失败超限，禁止访问1小时']);
            }
        } else {
            Cache::put($cacheKey, 1, 60);
        }

        if (!$username || !$password) {
            Cache::increment($cacheKey);

            return Response::json(['status' => 'fail', 'data' => [], 'message' => '账号或密码错误']);
        }

        $user = User::query()->where('username', $username)->where('password', md5($password))->where('status', '>=', 0)->first();
        if (!$user) {
            Cache::increment($cacheKey);

            return Response::json(['status' => 'fail', 'data' => [], 'message' => '账号不存在或已被禁用']);
        }

        DB::beginTransaction();
        try {
            // 如果未生成过订阅链接则生成一个
            $subscribe = UserSubscribe::query()->where('user_id', $user->id)->first();
            if (!$subscribe) {
                $code = $this->makeSubscribeCode();

                $subscribe = new UserSubscribe();
                $subscribe->user_id = $user->id;
                $subscribe->code = $code;
                $subscribe->times = 0;
                $subscribe->save();
            } else {
                $code = $subscribe->code;
            }

            // 更新订阅链接访问次数
            $subscribe->increment('times', 1);

            // 记录每次请求
            $this->log($subscribe->id, getClientIp(), 'API访问');

            // 处理用户信息
            unset($user->password, $user->remember_token);
            $data['user'] = $user;

            // 订阅链接
            $data['link'] = self::$systemConfig['subscribe_domain'] ? self::$systemConfig['subscribe_domain'] . '/s/' . $code : self::$systemConfig['website_url'] . '/s/' . $code;

            DB::commit();

            return Response::json(['status' => 'success', 'data' => $data, 'message' => '登录成功']);
        } catch (\Exception $e) {
            DB::rollBack();

            return Response::json(['status' => 'success', 'data' => [], 'message' => '登录失败']);
        }
    }

    // 写入订阅访问日志
    private function log($subscribeId, $ip, $headers)
    {
        $log = new UserSubscribeLog();
        $log->sid = $subscribeId;
        $log->request_ip = $ip;
        $log->request_time = date('Y-m-d H:i:s');
        $log->request_header = $headers;
        $log->save();
    }
}