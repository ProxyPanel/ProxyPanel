<?php

namespace App\Http\Controllers;

use App\Http\Models\User;
use App\Http\Models\UserScoreLog;
use Illuminate\Http\Request;
use Response;
use Redirect;
use Captcha;
use Cache;

/**
 * 登录控制器
 * Class LoginController
 * @package App\Http\Controllers
 */
class LoginController extends BaseController
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    // 登录页
    public function index(Request $request)
    {
        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));
            $password = trim($request->get('password'));
            $captcha = trim($request->get('captcha'));

            if (empty($username) || empty($password)) {
                $request->session()->flash('errorMsg', '请输入用户名和密码');

                return Redirect::back();
            }

            // 是否校验验证码
            if (self::$config['is_captcha']) {
                if (!Captcha::check($captcha)) {
                    $request->session()->flash('errorMsg', '验证码错误，请重新输入');

                    return Redirect::back()->withInput();
                }
            }

            $user = User::query()->where('username', $username)->where('password', md5($password))->first();
            if (!$user) {
                $request->session()->flash('errorMsg', '用户名或密码错误');

                return Redirect::back()->withInput();
            } else if ($user->status < 0) {
                $request->session()->flash('errorMsg', '账号已禁用');

                return Redirect::back();
            } else if ($user->status == 0 && self::$config['is_active_register'] && $user->is_admin == 0) {
                $request->session()->flash('errorMsg', '账号未激活，请先<a href="/activeUser?username=' . $user->username . '" target="_blank"><span style="color:#000">【激活账号】</span></a>');

                return Redirect::back()->withInput();
            }

            // 更新登录信息
            User::query()->where('id', $user['id'])->update(['last_login' => time()]);

            // 登录送积分
            if (self::$config['login_add_score']) {
                if (!Cache::has('loginAddScore_' . md5($username))) {
                    $score = mt_rand(self::$config['min_rand_score'], self::$config['max_rand_score']);
                    $ret = User::query()->where('id', $user['id'])->increment('score', $score);
                    if ($ret) {
                        $obj = new UserScoreLog();
                        $obj->user_id = $user['id'];
                        $obj->before = $user['score'];
                        $obj->after = $user['score'] + $score;
                        $obj->score = $score;
                        $obj->desc = '登录送积分';
                        $obj->created_at = date('Y-m-d H:i:s');
                        $obj->save();

                        $ttl = !empty(self::$config['login_add_score_range']) ? self::$config['login_add_score_range'] : 1440;
                        Cache::put('loginAddScore_' . md5($username), '1', $ttl);
                        $request->session()->flash('successMsg', '欢迎回来，系统自动赠送您 ' . $score . ' 积分，您可以用它兑换流量包');
                    }
                }
            }

            // 重新取出用户信息
            $user = User::query()->where('id', $user['id'])->first();

            $request->session()->put('user', $user->toArray());

            // 根据权限跳转
            if ($user['is_admin']) {
                return Redirect::to('admin');
            }

            return Redirect::to('user');
        } else {
            $view['is_captcha'] = self::$config['is_captcha'];
            $view['is_register'] = self::$config['is_register'];

            return Response::view('login', $view);
        }
    }

    // 退出
    public function logout(Request $request)
    {
        $request->session()->flush();

        return Redirect::to('login');
    }

}
