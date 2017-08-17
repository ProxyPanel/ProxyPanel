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
            } else if (empty($code)) {
                $request->session()->flash('errorMsg', '请输入邀请码');

                return Redirect::back()->withInput();
            } else if (md5($password) != md5($repassword)) {
                $request->session()->flash('errorMsg', '两次输入密码不一致，请重新输入');

                return Redirect::back()->withInput($request->except(['password', 'repassword']));
            }

            // 校验邀请码合法性
            $code = Invite::where('code', $code)->where('status', 0)->first();
            if (empty($code)) {
                $request->session()->flash('errorMsg', '邀请码不可用，请更换邀请码后重试');

                return Redirect::back()->withInput($request->except(['code']));
            }

            // 校验用户名是否已存在
            $exists = User::where('username', $username)->first();
            if ($exists) {
                $request->session()->flash('errorMsg', '用户名已存在，请更换用户名');

                return Redirect::back()->withInput();
            }

            // 最后一个可用端口
            $config = $this->systemConfig();
            $last_user = User::orderBy('id', 'desc')->first();
            $port = $config['is_rand_port'] ? $this->getRandPort() : $last_user->port + 1;

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
            if ($obj->id) {
                Invite::where('id', $code->id)->update(['status' => 1]);
            }

            return Redirect::to('login');
        } else {
            return Response::view('register');
        }
    }


}
