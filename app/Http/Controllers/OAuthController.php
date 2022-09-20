<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\IP;
use App\Models\User;
use App\Models\UserOauth;
use Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Str;

class OAuthController extends Controller
{
    public function route(Request $request, string $type)
    {
        $action = $request->input('action');
        $key = "services.{$type}.redirect";
        if ($action === 'binding') {
            config([$key => route('oauth.bind', ['type' => $type])]);
        } elseif ($action === 'register') {
            config([$key => route('oauth.register', ['type' => $type])]);
        } else {
            config([$key => route('oauth.login', ['type' => $type])]);
        }

        return Socialite::driver($type)->redirect();
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

        return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
    }

    private function bind(string $type, $user, $info)
    {
        $auth = $user->userAuths()->whereType($type)->first();
        $data = ['type' => $type, 'identifier' => $info->getId(), 'credential' => $info->token];
        if ($auth) {
            $user->userAuths()->whereType($type)->update($data);

            return redirect()->route('profile')->with('successMsg', trans('auth.oauth.rebind_success'));
        }

        $user->userAuths()->create($data);

        return redirect()->route('profile')->with('successMsg', trans('auth.oauth.bind_success'));
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
            return redirect()->route('profile')->with('successMsg', trans('auth.oauth.unbind_success'));
        }

        return redirect()->route('profile')->with('successMsg', trans('auth.oauth.unbind_failed'));
    }

    public function binding($type)
    {
        config(["services.{$type}.redirect" => route('oauth.bind', ['type' => $type])]);
        $info = Socialite::driver($type)->stateless()->user();

        if ($info) {
            $user = Auth::user();
            if ($user) {
                return $this->bind($type, $user, $info);
            }

            return redirect()->route('profile')->withErrors(trans('auth.oauth.bind_failed'));
        }

        return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
    }

    public function logining($type)
    {
        config(["services.{$type}.redirect" => route('oauth.login', ['type' => $type])]);
        $info = Socialite::driver($type)->stateless()->user();
        if ($info) {
            return $this->login($type, $info);
        }

        return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
    }

    public function register($type)
    {
        config(["services.{$type}.redirect" => route('oauth.register', ['type' => $type])]);
        $info = Socialite::driver($type)->stateless()->user();

        // 排除重复用户注册
        if ($info) {
            $user = User::whereUsername($info->getEmail())->first();
            if (! $user) {
                $user = UserOauth::whereIdentifier($info->getId())->first();
                if (! $user) {
                    $user = Helpers::addUser($info->getEmail(), Str::random(), MB * ((int) sysConfig('default_traffic')), null, $info->getNickname());

                    if ($user) {
                        $user->userAuths()->create([
                            'type'       => $type,
                            'identifier' => $info->getId(),
                            'credential' => $info->token,
                        ]);

                        Auth::login($user);

                        return redirect()->route('login');
                    }

                    return redirect()->route('register')->withErrors(trans('auth.oauth.register_failed'));
                }
            }

            return redirect()->route('login')->withErrors(trans('auth.oauth.registered'));
        }

        return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
    }
}
