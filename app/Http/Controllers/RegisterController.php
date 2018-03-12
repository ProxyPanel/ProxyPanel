<?php

namespace App\Http\Controllers;

use App\Http\Models\Invite;
use App\Http\Models\SsConfig;
use App\Http\Models\User;
use App\Http\Models\Verify;
use Illuminate\Http\Request;
use App\Mail\activeUser;
use Captcha;
use Response;
use Redirect;
use Mail;

/**
 * 注册控制器
 * Class LoginController
 * @package App\Http\Controllers
 */
class RegisterController extends Controller
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    // 注册页
    public function index(Request $request)
    {
        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));
            $password = trim($request->get('password'));
            $repassword = trim($request->get('repassword'));
            $captcha = trim($request->get('captcha'));
            $code = trim($request->get('code'));
            $register_token = $request->get('register_token');
            $aff = intval($request->get('aff', 0));

            // 防止重复提交
            $session_register_token = $request->session()->get('register_token');
            if (empty($register_token) || $register_token != $session_register_token) {
                $request->session()->flash('errorMsg', '请勿重复请求，刷新一下页面再试试');

                return Redirect::back()->withInput();
            } else {
                $request->session()->forget('register_token');
            }

            if (empty($username)) {
                $request->session()->flash('errorMsg', '请输入用户名');

                return Redirect::back()->withInput();
            } else if (empty($password)) {
                $request->session()->flash('errorMsg', '请输入密码');

                return Redirect::back()->withInput();
            } else if (empty($repassword)) {
                $request->session()->flash('errorMsg', '请重新输入密码');

                return Redirect::back()->withInput();
            } else if (md5($password) != md5($repassword)) {
                $request->session()->flash('errorMsg', '两次输入密码不一致，请重新输入');

                return Redirect::back()->withInput($request->except(['password', 'repassword']));
            } else if (false === filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $request->session()->flash('errorMsg', '用户名必须是合法邮箱，请重新输入');

                return Redirect::back()->withInput();
            }

            // 是否校验验证码
            if (self::$config['is_captcha']) {
                if (!Captcha::check($captcha)) {
                    $request->session()->flash('errorMsg', '验证码错误，请重新输入');

                    return Redirect::back()->withInput($request->except(['password', 'repassword']));
                }
            }

            // 是否开启注册
            if (!self::$config['is_register']) {
                $request->session()->flash('errorMsg', '系统维护暂停注册');

                return Redirect::back();
            }

            // 如果需要邀请注册
            if (self::$config['is_invite_register']) {
                if (empty($code)) {
                    $request->session()->flash('errorMsg', '请输入邀请码');

                    return Redirect::back()->withInput();
                }

                // 校验邀请码合法性
                $code = Invite::query()->where('code', $code)->where('status', 0)->first();
                if (empty($code)) {
                    $request->session()->flash('errorMsg', '邀请码不可用，请更换邀请码后重试');

                    return Redirect::back()->withInput($request->except(['code']));
                }
            }

            // 校验用户名是否已存在
            $exists = User::query()->where('username', $username)->first();
            if ($exists) {
                $request->session()->flash('errorMsg', '用户名已存在，请更换用户名');

                return Redirect::back()->withInput();
            }

            // 校验aff对应账号是否存在
            if ($aff) {
                $affUser = User::query()->where('id', $aff)->first();
                $referral_uid = $affUser ? $aff : 0;
            } else {
                $referral_uid = 0;
            }

            // 最后一个可用端口
            $last_user = User::query()->orderBy('id', 'desc')->first();
            $port = self::$config['is_rand_port'] ? $this->getRandPort() : $last_user->port + 1;

            // 默认加密方式、协议、混淆
            $method = SsConfig::query()->where('type', 1)->where('is_default', 1)->first();
            $protocol = SsConfig::query()->where('type', 2)->where('is_default', 1)->first();
            $obfs = SsConfig::query()->where('type', 3)->where('is_default', 1)->first();

            // 创建新用户
            $transfer_enable = $referral_uid ? (self::$config['default_traffic'] + self::$config['referral_traffic']) * 1048576 : self::$config['default_traffic'] * 1048576;
            $user = new User();
            $user->username = $username;
            $user->password = md5($password);
            $user->port = $port;
            $user->passwd = makeRandStr();
            $user->transfer_enable = $transfer_enable;
            $user->method = $method ? $method->name : 'aes-192-ctr';
            $user->protocol = $protocol ? $protocol->name : 'auth_chain_a';
            $user->obfs = $obfs ? $obfs->name : 'tls1.2_ticket_auth';
            $user->enable_time = date('Y-m-d H:i:s');
            $user->expire_time = date('Y-m-d H:i:s', strtotime("+" . self::$config['default_days'] . " days"));
            $user->reg_ip = $request->getClientIp();
            $user->referral_uid = $referral_uid;
            $user->save();

            // 更新邀请码
            if (self::$config['is_invite_register'] && $user->id) {
                Invite::query()->where('id', $code->id)->update(['fuid' => $user->id, 'status' => 1]);
            }

            // 发送邮件
            if (self::$config['is_active_register']) {
                // 生成激活账号的地址
                $token = md5(self::$config['website_name'] . $username . microtime());
                $verify = new Verify();
                $verify->user_id = $user->id;
                $verify->username = $username;
                $verify->token = $token;
                $verify->status = 0;
                $verify->save();

                $activeUserUrl = self::$config['website_url'] . '/active/' . $token;
                $title = '注册激活';
                $content = '请求地址：' . $activeUserUrl;

                try {
                    Mail::to($username)->send(new activeUser(self::$config['website_name'], $activeUserUrl));
                    $this->sendEmailLog($user->id, $title, $content);
                } catch (\Exception $e) {
                    $this->sendEmailLog($user->id, $title, $content, 0, $e->getMessage());
                }

                $request->session()->flash('regSuccessMsg', '注册成功：激活邮件已发送，请查看邮箱');
            } else {
                $request->session()->flash('regSuccessMsg', '注册成功');
            }

            return Redirect::to('login');
        } else {
            $request->session()->put('register_token', makeRandStr(16));

            $view['is_captcha'] = self::$config['is_captcha'];
            $view['is_register'] = self::$config['is_register'];
            $view['is_invite_register'] = self::$config['is_invite_register'];

            return Response::view('register', $view);
        }
    }


}
