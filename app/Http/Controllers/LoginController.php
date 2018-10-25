<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\QQWry;
use App\Http\Models\User;
use App\Http\Models\UserLoginLog;
use Illuminate\Http\Request;
use Response;
use Redirect;
use Captcha;
use Session;
use Cache;
use Log;
use Auth;

/**
 * 登录控制器
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

    // 登录页
    public function index(Request $request)
    {
        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));
            $password = trim($request->get('password'));
            $captcha = trim($request->get('captcha'));
            $remember = trim($request->get('remember'));

            if (empty($username) || empty($password)) {
                Session::flash('errorMsg', '请输入用户名和密码');

                return Redirect::back();
            }

            // 是否校验验证码
            if (self::$systemConfig['is_captcha']) {
                if (!Captcha::check($captcha)) {
                    Session::flash('errorMsg', '验证码错误，请重新输入');

                    return Redirect::back()->withInput();
                }
            }

            if (!Auth::attempt(['username' => $username, 'password' => $password], $remember)) {
                Session::flash('errorMsg', '用户名或密码错误');

                return Redirect::back()->withInput();
            } elseif (!Auth::user()->is_admin && Auth::user()->status < 0) {
                Session::flash('errorMsg', '账号已禁用');

                return Redirect::back();
            } elseif (Auth::user()->status == 0 && self::$systemConfig['is_active_register'] && Auth::user()->is_admin == 0) {
                Session::flash('errorMsg', '账号未激活，请点击<a href="/activeUser?username=' . Auth::user()->username . '" target="_blank"><span style="color:#000">【激活账号】</span></a>');

                return Redirect::back()->withInput();
            }

            // 登录送积分
            if (self::$systemConfig['login_add_score']) {
                if (!Cache::has('loginAddScore_' . md5($username))) {
                    $score = mt_rand(self::$systemConfig['min_rand_score'], self::$systemConfig['max_rand_score']);
                    $ret = User::query()->where('id', Auth::user()->id)->increment('score', $score);
                    if ($ret) {
                        $this->addUserScoreLog(Auth::user()->id, Auth::user()->score, Auth::user()->score + $score, $score, '登录送积分');

                        // 登录多久后再登录可以获取积分
                        $ttl = self::$systemConfig['login_add_score_range'] ? self::$systemConfig['login_add_score_range'] : 1440;
                        Cache::put('loginAddScore_' . md5($username), '1', $ttl);

                        Session::flash('successMsg', '欢迎回来，系统自动赠送您 ' . $score . ' 积分，您可以用它兑换流量');
                    }
                }
            }

            // 写入登录日志
            $this->addUserLoginLog(Auth::user()->id, getClientIp());

            // 更新登录信息
            User::query()->where('id', Auth::user()->id)->update(['last_login' => time()]);

            // 根据权限跳转
            if (Auth::user()->is_admin) {
                return Redirect::to('admin');
            }

            return Redirect::to('/');
        } else {
            if (Auth::viaRemember()) {
                if (Auth::check()) {
                    if (Auth::user()->is_admin) {
                        return Redirect::to('admin');
                    }

                    return Redirect::to('/');
                }
            }

            $view['is_captcha'] = self::$systemConfig['is_captcha'];
            $view['is_register'] = self::$systemConfig['is_register'];
            $view['website_home_logo'] = self::$systemConfig['website_home_logo'];
            $view['website_analytics'] = self::$systemConfig['website_analytics'];
            $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

            return Response::view('login', $view);
        }
    }

    // 退出
    public function logout(Request $request)
    {
        Auth::logout();

        return Redirect::to('login');
    }

    // 添加用户登录日志
    private function addUserLoginLog($userId, $ip)
    {
        // 解析IP信息
        $qqwry = new QQWry();
        $ipInfo = $qqwry->ip($ip);
        if (isset($ipInfo['error'])) {
            Log::info('无法识别IP，可能是IPv6，尝试解析：' . $ip);
            $ipInfo = getIPv6($ip);
        }

        if (empty($ipInfo) || empty($ipInfo['country'])) {
            \Log::warning("获取IP地址信息异常：" . $ip);
        }

        $log = new UserLoginLog();
        $log->user_id = $userId;
        $log->ip = $ip;
        $log->country = $ipInfo['country'] ?? '';
        $log->province = $ipInfo['province'] ?? '';
        $log->city = $ipInfo['city'] ?? '';
        $log->county = $ipInfo['county'] ?? '';
        $log->isp = $ipInfo['isp'] ?? ($ipInfo['organization'] ?? '');
        $log->area = $ipInfo['area'] ?? '';
        $log->save();
    }
}
