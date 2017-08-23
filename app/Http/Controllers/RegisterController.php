<?php

namespace App\Http\Controllers;

use App\Http\Models\Invite;
use App\Http\Models\User;
use Illuminate\Http\Request;
use Response;
use Redirect;

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

            // 最后一个可用端口
            $last_user = User::orderBy('id', 'desc')->first();
            $port = self::$config['is_rand_port'] ? $this->getRandPort() : $last_user->port + 1;

            // 创建新用户
            $obj = new User();
            $obj->username = $username;
            $obj->password = md5($password);
            $obj->port = $port;
            $obj->passwd = $this->makeRandStr();
            $obj->transfer_enable = $this->toGB(1);
            $obj->enable_time = date('Y-m-d H:i:s');
            $obj->expire_time = date('Y-m-d H:i:s', strtotime("+30 days"));
            $obj->reg_ip = $request->getClientIp();
            $obj->save();

            // 更新邀请码
            if (self::$config['is_invite_register'] && $obj->id) {
                Invite::where('id', $code->id)->update(['fuid' => $obj->id,'status' => 1]);
            }

            return Redirect::to('login');
        } else {
            $view['is_register'] = self::$config['is_register'];
            $view['is_invite_register'] = self::$config['is_invite_register'];

            return Response::view('register', $view);
        }
    }


}
