<?php

namespace App\Http\Controllers;

use App\Http\Models\User;
use Illuminate\Http\Request;
use Response;
use Redirect;

/**
 * 登录控制器
 * Class LoginController
 * @package App\Http\Controllers
 */
class LoginController extends Controller
{
    // 登录页
    public function index(Request $request)
    {
        if ($request->method() == 'POST') {
            $username = $request->get('username');
            $password = $request->get('password');

            if (empty($username) || empty($password)) {
                $request->session()->flash('error_msg', '请输入用户名和密码');
                return Redirect::to('login');
            }

            $user = User::where('username', $username)->where('password', md5($password))->where('is_admin', 1)->first();
            if (!$user) {
                $request->session()->flash('error_msg', '用户名或密码错误');
                return Redirect::to('login');
            }

            $request->session()->put('user', $user->toArray());

            return Redirect::to('admin');
        } else {
            return Response::view('login');
        }
    }

    // 退出
    public function logout(Request $request)
    {
        $request->session()->flush();

        return Redirect::to('login');
    }

}
