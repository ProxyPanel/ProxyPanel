<?php

namespace App\Http\Controllers\OAuth;

use App\Components\Helpers;
use App\Components\IP;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLoginLog;
use App\Models\UserOauth;
use Auth;
use Laravel\Socialite\Facades\Socialite;
use Log;
use Redirect;
use Str;

class BaseController extends Controller
{
    public function route($type, $action = null)
    {
        if ($action === 'binding') {
            return Socialite::driver($type)->with(['redirect_uri' => route('oauth.bind', ['type' => $type])])->redirect();
        }

        if ($action === 'register') {
            return Socialite::driver($type)->with(['redirect_uri' => route('oauth.register', ['type' => $type])])->redirect();
        }

        return Socialite::driver($type)->with(['redirect_uri' => route('oauth.redirect', ['type' => $type])])->redirect();
    }

    public function redirect($type)
    {
        $info = Socialite::driver($type)->user();
        if ($info) {
            $user = User::whereUsername($info->getEmail())->first();
            if (! $user) {
                $user = UserOauth::whereIdentifier($info->getId())->first();
                if ($user) {
                    $user = $user->user;
                }
            }
        }

        if (isset($user)) {
            Auth::login($user);
            // 写入登录日志
            $this->addUserLoginLog($user->id, IP::getClientIp());

            // 更新登录信息
            $user->update(['last_login' => time()]);

            return Redirect::route('login');
        }

        return Redirect::route('login')->withErrors(trans('auth.error.not_found_user'));
    }

    /**
     * 添加用户登录日志.
     *
     * @param  int  $userId  用户ID
     * @param  string  $ip  IP地址
     */
    private function addUserLoginLog(int $userId, string $ip): void
    {
        $ipLocation = IP::getIPInfo($ip);

        if (empty($ipLocation) || empty($ipLocation['country'])) {
            Log::warning(trans('error.get_ip').'：'.$ip);
        }

        $log = new UserLoginLog();
        $log->user_id = $userId;
        $log->ip = $ip;
        $log->country = $ipLocation['country'] ?? '';
        $log->province = $ipLocation['province'] ?? '';
        $log->city = $ipLocation['city'] ?? '';
        $log->county = $ipLocation['county'] ?? '';
        $log->isp = $ipLocation['isp'] ?? ($ipLocation['organization'] ?? '');
        $log->area = $ipLocation['area'] ?? '';
        $log->save();
    }

    public function bind($type)
    {
        $user = Auth::user();
        $info = Socialite::driver($type)->stateless()->user();

        if ($user) {
            if ($info) {
                $user->userAuths()->create([
                    'type'       => $type,
                    'identifier' => $info->getId(),
                    'credential' => $info->token,
                ]);

                return redirect()->route('profile')->with('successMsg', '绑定成功');
            }

            return redirect()->route('profile')->withErrors('绑定失败');
        }

        return redirect()->route('profile')->withErrors('无用户');
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

        return redirect()->route('register')->withErrors('绑定失败');
    }
}
