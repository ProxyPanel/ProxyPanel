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
    public function unbind(string $provider): RedirectResponse
    {
        $user = Auth::user();

        if ($user && $user->userAuths()->whereType($provider)->delete()) {
            return redirect()->route('profile')->with('successMsg', trans('auth.oauth.unbind_success'));
        }

        return redirect()->route('profile')->withErrors(trans('auth.oauth.unbind_failed'));
    }

    public function bind(string $provider): RedirectResponse
    {
        config(["services.$provider.redirect" => route('oauth.bind', ['provider' => $provider])]);
        $authInfo = Socialite::driver($provider)->stateless()->user();

        if (! $authInfo) {
            return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
        }

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('profile')->withErrors(trans('auth.oauth.bind_failed'));
        }

        return $this->bindLogic($provider, $user, $authInfo);
    }

    private function bindLogic(string $provider, User $user, \Laravel\Socialite\Contracts\User $authInfo): RedirectResponse
    {
        $data = [
            'type' => $provider,
            'identifier' => $authInfo->getId(),
            'credential' => $authInfo->token,
        ];

        $auth = $user->userAuths()->whereType($provider)->first();

        if ($auth) {
            $user->userAuths()->whereType($provider)->update($data);
            $message = trans('auth.oauth.rebind_success');
        } else {
            $user->userAuths()->create($data);
            $message = trans('auth.oauth.bind_success');
        }

        return redirect()->route('profile')->with('successMsg', $message);
    }

    public function register(string $provider): RedirectResponse
    {
        config(["services.$provider.redirect" => route('oauth.register', ['provider' => $provider])]);
        if (! sysConfig('is_register')) {
            return redirect()->route('register')->withErrors(trans('auth.register.error.disable'));
        }

        if ((int) sysConfig('is_invite_register') === 2) {
            return redirect()->route('register')->withErrors(trans('validation.required', ['attribute' => trans('auth.invite.attribute')]));
        }

        $registerInfo = Socialite::driver($provider)->stateless()->user();

        if (! $registerInfo) {
            return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
        }

        $user = User::whereUsername($registerInfo->getEmail())->first();

        if (! $user) {  // 邮箱未被注册
            $userAuth = UserOauth::whereType($provider)->whereIdentifier($registerInfo->getId())->first();

            if (! $userAuth) { // 第三方账号未被绑定
                $user = Helpers::addUser($registerInfo->getEmail(), Str::random(), MiB * sysConfig('default_traffic'), (int) sysConfig('default_days'), $registerInfo->getNickname());

                $user->userAuths()->create([
                    'type' => $provider,
                    'identifier' => $registerInfo->getId(),
                    'credential' => $registerInfo->token,
                ]);

                return $this->handleLogin($user);
            }
        }

        return redirect()->route('login')->withErrors(trans('auth.oauth.registered'));
    }

    private function handleLogin(User $user): RedirectResponse
    {
        Auth::login($user);
        Helpers::userLoginAction($user, IP::getClientIp());

        return redirect()->route('login');
    }

    public function login(string $provider): RedirectResponse
    {
        config(["services.$provider.redirect" => route('oauth.login', ['provider' => $provider])]);
        $authInfo = Socialite::driver($provider)->stateless()->user();

        if ($authInfo) {
            $auth = UserOauth::whereType($provider)->whereIdentifier($authInfo->getId())->first();

            if ($auth && ($user = $auth->user)) { // 如果第三方登录有记录，直接登录用户
                return $this->handleLogin($user);
            }

            $user = User::whereUsername($authInfo->getEmail())->first();

            if ($user) { // 如果用户存在，执行绑定逻辑并登录用户
                $this->bindLogic($provider, $user, $authInfo);

                return $this->handleLogin($user);
            }

            // 如果用户不存在，则返回错误消息
            return redirect()->route('login')->withErrors(trans('auth.error.not_found_user'));
        }

        return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
    }

    public function redirect(string $provider, string $operation = 'login'): RedirectResponse
    {
        $redirectRoutes = [
            'bind' => 'oauth.bind',
            'register' => 'oauth.register',
            'login' => 'oauth.login',
        ];

        $key = "services.$provider.redirect";
        config([$key => route($redirectRoutes[$operation], ['provider' => $provider])]);

        return Socialite::driver($provider)->stateless()->redirect();
    }
}
