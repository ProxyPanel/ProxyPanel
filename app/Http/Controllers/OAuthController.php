<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Models\User;
use App\Models\UserOauth;
use App\Utils\Helpers;
use App\Utils\IP;
use Hashids\Hashids;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Str;

class OAuthController extends Controller
{
    public function unbind(string $provider): RedirectResponse
    {
        $user = auth()->user();

        if ($user && $user->userAuths()->whereType($provider)->delete()) {
            return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('user.oauth.unbind')]));
        }

        return redirect()->back()->withErrors(trans('common.failed_item', ['attribute' => trans('user.oauth.unbind')]));
    }

    public function bind(string $provider): RedirectResponse
    {
        config(["services.$provider.redirect" => route('oauth.bind', ['provider' => $provider])]);
        $authInfo = Socialite::driver($provider)->stateless()->user();

        if (! $authInfo) {
            return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
        }

        $user = auth()->user();

        if (! $user) {
            return redirect()->back()->withErrors(trans('common.failed_item', ['attribute' => trans('user.oauth.bind')]));
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
            $message = trans('common.success_item', ['attribute' => trans('user.oauth.rebind')]);
        } else {
            $user->userAuths()->create($data);
            $message = trans('common.success_item', ['attribute' => trans('user.oauth.bind')]);
        }

        return redirect()->back()->with('successMsg', $message);
    }

    public function register(string $provider): RedirectResponse
    {
        config(["services.$provider.redirect" => route('oauth.register', ['provider' => $provider])]);
        if (! sysConfig('is_register')) {
            return redirect()->route('register')->withErrors(trans('auth.register.error.disable'));
        }

        if ((int) sysConfig('is_invite_register') === 2) {
            return redirect()->route('register')->withErrors(trans('validation.required', ['attribute' => trans('user.invite.attribute')]));
        }

        $registerInfo = Socialite::driver($provider)->stateless()->user();

        if (! $registerInfo) {
            return redirect()->route('login')->withErrors(trans('auth.oauth.login_failed'));
        }

        $user = User::whereUsername($registerInfo->getEmail())->first();

        if (! $user) {  // 邮箱未被注册
            $userAuth = UserOauth::whereType($provider)->whereIdentifier($registerInfo->getId())->first();

            if (! $userAuth) { // 第三方账号未被绑定
                // 获取邀请信息
                $affArr = $this->getAff();
                $inviter_id = $affArr['inviter_id'];

                // 计算流量值（包括邀请奖励流量）
                $transfer_enable = MiB * ((int) sysConfig('default_traffic') + ($inviter_id ? (int) sysConfig('referral_traffic') : 0));

                // 创建用户并传入邀请者 ID
                $user = Helpers::addUser($registerInfo->getEmail(), Str::random(), $transfer_enable, (int) sysConfig('default_days'), $inviter_id, $registerInfo->getNickname(), 1);

                // 更新邀请码（如果使用了邀请码）
                if ($affArr['code_id'] && sysConfig('is_invite_register')) {
                    Invite::find($affArr['code_id'])?->update(['invitee_id' => $user->id, 'status' => 1]);
                }

                // 清除邀请人Cookie
                cookie()->unqueue('register_aff');

                // 给邀请人增加流量（如果有的话）
                if ($inviter_id) {
                    $referralUser = User::find($inviter_id);
                    if ($referralUser && $referralUser->expiration_date >= date('Y-m-d')) {
                        $referralUser->incrementData(sysConfig('referral_traffic') * MiB);
                    }
                }

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

    private function getAff(): array
    { // 获取邀请信息
        $data = ['inviter_id' => null, 'code_id' => 0]; // 邀请人ID 与 邀请码ID

        // 检查cookie中的邀请信息（通过Affiliate中间件设置）
        $cookieAff = request()?->cookie('register_aff');
        if ($cookieAff) {
            $data['inviter_id'] = $this->setInviter($cookieAff);
        }

        return $data;
    }

    private function setInviter(string|int $aff): ?int
    {
        $uid = 0;
        if (is_numeric($aff)) {
            $uid = (int) $aff;
        } else {
            $decode = (new Hashids(sysConfig('affiliate_link_salt'), 8))->decode($aff);
            if ($decode) {
                $uid = $decode[0];
            }
        }

        return $uid && User::whereId($uid)->exists() ? $uid : null;
    }

    private function handleLogin(User $user): RedirectResponse
    {
        auth()->login($user);
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
