<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOauth;
use App\Utils\Helpers;
use App\Utils\IP;
use Auth;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Str;

class OAuthController extends Controller
{
    public function simple(string $type): RedirectResponse
    {
        $info = Socialite::driver($type)->stateless()->user();
        if ($info) {
            $user = Auth::user();

            if ($user) {
                return $this->binding($type, $user, $info);
            }

            return $this->logging($type, $info);
        }

        return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
    }

    private function binding(string $type, User $user, \Laravel\Socialite\Contracts\User $OauthUser): RedirectResponse
    {
        $data = ['type' => $type, 'identifier' => $OauthUser->getId(), 'credential' => $OauthUser->token];
        if ($user->userAuths()->whereType($type)->updateOrCreate($data)) {
            return redirect()->route('profile')->with('successMsg', trans('auth.oauth.bind_success'));
        }

        return redirect()->route('profile')->withErrors(trans('auth.oauth.bind_failed'));
    }

    private function logging(string $type, \Laravel\Socialite\Contracts\User $OauthUser): RedirectResponse
    {
        $user = User::whereUsername($OauthUser->getEmail())->first();
        if (! isset($user)) {
            $auth = UserOauth::whereType($type)->whereIdentifier($OauthUser->getId())->first();
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

    public function login(string $type): RedirectResponse
    {
        $info = Socialite::driver($type)->stateless()->user();
        if ($info) {
            return $this->logging($type, $info);
        }

        return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
    }

    public function unbind(string $type): RedirectResponse
    {
        $user = Auth::user();
        if ($user && $user->userAuths()->whereType($type)->delete()) {
            return redirect()->route('profile')->with('successMsg', trans('auth.oauth.unbind_success'));
        }

        return redirect()->route('profile')->with('successMsg', trans('auth.oauth.unbind_failed'));
    }

    public function bind(string $type): RedirectResponse
    {
        $info = Socialite::driver($type)->stateless()->user();

        if ($info) {
            $user = Auth::user();
            if ($user) {
                return $this->binding($type, $user, $info);
            }

            return redirect()->route('profile')->withErrors(trans('auth.oauth.bind_failed'));
        }

        return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
    }

    public function register(string $type): RedirectResponse
    {
        if (! sysConfig('is_register')) {
            return redirect()->route('register')->withErrors(trans('auth.register.error.disable'));
        }

        if ((int) sysConfig('is_invite_register') === 2) { // 必须使用邀请码
            return redirect()->route('register')->withErrors(trans('validation.required', ['attribute' => trans('auth.invite.attribute')]));
        }

        $OauthUser = Socialite::driver($type)->stateless()->user();

        if ($OauthUser) {
            if (User::whereUsername($OauthUser->getEmail())->doesntExist() && UserOauth::whereIdentifier($OauthUser->getId())->doesntExist()) { // 排除重复用户注册
                $user = Helpers::addUser($OauthUser->getEmail(), Str::random(), MiB * ((int) sysConfig('default_traffic')), null, $OauthUser->getNickname());

                $user->userAuths()->create([
                    'type' => $type,
                    'identifier' => $OauthUser->getId(),
                    'credential' => $OauthUser->token,
                ]);

                Auth::login($user);

                return redirect()->route('login');
            }

            return redirect()->route('login')->withErrors(trans('auth.oauth.registered'));
        }

        return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
    }
}
