<?php

namespace App\Http\Controllers\OAuth;

use App\Components\Helpers;
use App\Components\IP;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOauth;
use Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Str;

class BaseController extends Controller
{
    public function route(Request $request, string $type)
    {
        $action = $request->input('action');
        if ($action === 'binding') {
            return Socialite::driver($type)->with(['redirect_uri' => route('oauth.bind', ['type' => $type])])->redirect();
        }

        if ($action === 'register') {
            return Socialite::driver($type)->with(['redirect_uri' => route('oauth.register', ['type' => $type])])->redirect();
        }

        return Socialite::driver($type)->with(['redirect_uri' => route('oauth.login', ['type' => $type])])->redirect();
    }

    public function simple(string $type)
    {
        $info = Socialite::driver($type)->stateless()->user();
        if ($info) {
            $user = Auth::user();

            if ($user) {
                return $this->bind($type, $user, $info);
            }

            return $this->login($type, $info);
        }

        return redirect()->route('login')->withErrors('第三方登录失败！');
    }

    private function bind(string $type, $user, $info)
    {
        $auth = $user->userAuths()->whereType($type)->first();
        $data = ['type' => $type, 'identifier' => $info->getId(), 'credential' => $info->token];
        if ($auth) {
            $user->userAuths()->whereType($type)->update($data);

            return redirect()->route('profile')->with('successMsg', '重新绑定成功');
        }

        $user->userAuths()->create($data);

        return redirect()->route('profile')->with('successMsg', '绑定成功');
    }

    private function login(string $type, $info)
    {
        $user = User::whereUsername($info->getEmail())->first();
        if (! isset($user)) {
            $auth = UserOauth::whereType($type)->whereIdentifier($info->getId())->first();
            if (isset($auth)) {
                $user = $auth->user;
            }
        }

        if (isset($user)) {
            Auth::login($user);
            Helpers::userLoginAction($user, IP::getClientIp()); // 用户登录后操作

            return redirect()->route('login');
        }

        return redirect()->route('login')->withErrors(trans('auth.error.not_found_user'));
    }

    public function unsubscribe(string $type)
    {
        $user = Auth::user();
        if ($user && $user->userAuths()->whereType($type)->delete()) {
            return redirect()->route('profile')->with('successMsg', '解绑成功');
        }

        return redirect()->route('profile')->with('successMsg', '解绑失败');
    }

    public function binding($type)
    {
        $info = Socialite::driver($type)->stateless()->user();

        if ($info) {
            $user = Auth::user();
            if ($user) {
                return $this->bind($type, $user, $info);
            }

            return redirect()->route('profile')->withErrors('绑定失败');
        }

        return redirect()->route('login')->withErrors('第三方登录失败！');
    }

    public function logining($type)
    {
        $info = Socialite::driver($type)->stateless()->user();
        if ($info) {
            return $this->login($type, $info);
        }

        return redirect()->route('login')->withErrors('第三方登录失败！');
    }

    public function register($type)
    {
        $info = Socialite::driver($type)->stateless()->user();

        // 排除重复用户注册
        if ($info) {
            $user = User::whereUsername($info->getEmail())->first();
            if (! $user) {
                $user = UserOauth::whereIdentifier($info->getId())->first();
                if (! $user) {
                    $user = Helpers::addUser($info->getEmail(), Str::random(), MB * ((int) sysConfig('default_traffic')), null, $user->getNickname());

                    if ($user) {
                        $user->userAuths()->create([
                            'type'       => $type,
                            'identifier' => $info->getId(),
                            'credential' => $info->token,
                        ]);

                        Auth::login($user);

                        return redirect()->route('login');
                    }

                    return redirect()->route('register')->withErrors('注册失败');
                }
            }

            return redirect()->route('login')->withErrors('已注册，请直接登录');
        }

        return redirect()->route('login')->withErrors('第三方登录失败！');
    }
}
