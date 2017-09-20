<?php

namespace App\Http\Controllers;

use App\Http\Models\Invite;
use App\Http\Models\User;
use App\Http\Models\Verify;
use Illuminate\Http\Request;
use App\Mail\activeUser;
use Response;
use Redirect;
use Mail;

/**
 * 注册控制器
 * Class LoginController
 * @package App\Http\Controllers
 */
class RegisterController extends BaseController
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
            $code = trim($request->get('code'));
            $aff = intval($request->get('aff', 0));

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

            // 是否开启注册
            if (!self::$config['is_register']) {
                $request->session()->flash('errorMsg', '系统维护暂停注册，如需账号请联系管理员');

                return Redirect::back();
            }

            // 如果需要邀请注册
            if (self::$config['is_invite_register']) {
                if (empty($code)) {
                    $request->session()->flash('errorMsg', '请输入邀请码');

                    return Redirect::back()->withInput();
                }

                // 校验邀请码合法性
                $code = Invite::where('code', $code)->where('status', 0)->first();
                if (empty($code)) {
                    $request->session()->flash('errorMsg', '邀请码不可用，请更换邀请码后重试');

                    return Redirect::back()->withInput($request->except(['code']));
                }
            }

            // 校验用户名是否已存在
            $exists = User::where('username', $username)->first();
            if ($exists) {
                $request->session()->flash('errorMsg', '用户名已存在，请更换用户名');

                return Redirect::back()->withInput();
            }

            // 校验aff对应账号是否存在
            if ($aff) {
                $affUser = User::where('id', $aff)->first();
                if ($affUser) {
                    $referral_uid = $aff;
                } else {
                    $referral_uid = 0;
                }
            } else {
                $referral_uid = 0;
            }

            // 最后一个可用端口
            $last_user = User::orderBy('id', 'desc')->first();
            $port = self::$config['is_rand_port'] ? $this->getRandPort() : $last_user->port + 1;

            // 创建新用户
            $user = new User();
            $user->username = $username;
            $user->password = md5($password);
            $user->port = $port;
            $user->passwd = $this->makeRandStr();
            $user->transfer_enable = $this->toGB(1);
            $user->enable_time = date('Y-m-d H:i:s');
            $user->expire_time = date('Y-m-d H:i:s', strtotime("+30 days"));
            $user->reg_ip = $request->getClientIp();
            $user->referral_uid = $referral_uid;
            $user->save();

            // 更新邀请码
            if (self::$config['is_invite_register'] && $user->id) {
                Invite::where('id', $code->id)->update(['fuid' => $user->id, 'status' => 1]);

                // 生成激活账号的地址
                $token = md5(self::$config['website_name'] . $username . microtime());
                $verify = new Verify();
                $verify->user_id = $user->id;
                $verify->username = $username;
                $verify->token = $token;
                $verify->status = 0;
                $verify->save();

                // 发送邮件
                $activeUserUrl = self::$config['website_url'] . '/active/' . $token;
                Mail::to($username)->send(new activeUser(self::$config['website_name'], $activeUserUrl));

                $request->session()->flash('regSuccessMsg', '注册成功：激活邮件已发送，请查看邮箱');
            }

            return Redirect::to('login');
        } else {
            $view['is_register'] = self::$config['is_register'];
            $view['is_invite_register'] = self::$config['is_invite_register'];

            return Response::view('register', $view);
        }
    }


}
