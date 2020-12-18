<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\IP;
use App\Mail\activeUser;
use App\Mail\resetPassword;
use App\Mail\sendVerifyCode;
use App\Models\EmailFilter;
use App\Models\Invite;
use App\Models\User;
use App\Models\UserLoginLog;
use App\Models\Verify;
use App\Models\VerifyCode;
use Auth;
use Cache;
use Captcha;
use Cookie;
use Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use Mail;
use Redirect;
use Response;
use Session;
use Str;
use Validator;

/**
 * 认证控制器.
 *
 * Class AuthController
 */
class AuthController extends Controller
{
    // 登录
    public function login(Request $request)
    {
        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => trans('auth.email_null'),
                'password.required' => trans('auth.password_null'),
            ]);

            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator->errors());
            }

            $email = $request->input('email');
            $password = $request->input('password');
            $remember = $request->input('remember');

            // 是否校验验证码
            $captcha = $this->check_captcha($request);
            if ($captcha !== false) {
                return $captcha;
            }

            // 验证账号并创建会话
            if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
                return Redirect::back()->withInput()->withErrors(trans('auth.login_error'));
            }
            $user = Auth::getUser();

            if (! $user) {
                return Redirect::back()->withInput()->withErrors(trans('auth.login_error'));
            }

            // 校验普通用户账号状态
            if ($user->status < 0) {
                Auth::logout(); // 强制销毁会话，因为Auth::attempt的时候会产生会话

                return Redirect::back()->withInput()->withErrors(trans('auth.login_ban', ['email' => sysConfig('webmaster_email')]));
            }

            if ($user->status === 0 && sysConfig('is_activate_account')) {
                Auth::logout(); // 强制销毁会话，因为Auth::attempt的时候会产生会话

                return Redirect::back()->withInput()->withErrors(trans('auth.active_tip').'<a href="'.route('active').'?email='.$email.'" target="_blank"><span style="color:#000">【'.trans('auth.active_account').'】</span></a>');
            }

            // 写入登录日志
            $this->addUserLoginLog($user->id, IP::getClientIp());

            // 更新登录信息
            Auth::getUser()->update(['last_login' => time()]);

            // 根据权限跳转
            if ($user->hasPermissionTo('admin.index')) {
                return Redirect::route('admin.index');
            }

            return Redirect::route('home');
        }

        if (Auth::check()) {
            if (Auth::getUser()->hasPermissionTo('admin.index')) {
                return Redirect::route('admin.index');
            }

            return Redirect::route('home');
        }

        return view('auth.login');
    }

    // 校验验证码
    private function check_captcha($request)
    {
        switch (sysConfig('is_captcha')) {
            case 1: // 默认图形验证码
                if (! Captcha::check($request->input('captcha'))) {
                    return Redirect::back()->withInput()->withErrors(trans('auth.captcha_error'));
                }
                break;
            case 2: // Geetest
                $validator = Validator::make($request->all(), [
                    'geetest_challenge' => 'required|geetest',
                ], [
                    'geetest' => trans('auth.captcha_fail'),
                ]);

                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors(trans('auth.captcha_fail'));
                }
                break;
            case 3: // Google reCAPTCHA
                $validator = Validator::make($request->all(), [
                    'g-recaptcha-response' => 'required|NoCaptcha',
                ]);

                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors(trans('auth.captcha_fail'));
                }
                break;
            case 4: // hCaptcha
                $validator = Validator::make($request->all(), [
                    'h-captcha-response' => 'required|HCaptcha',
                ]);

                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors(trans('auth.captcha_fail'));
                }
                break;
            default: // 不启用验证码
                break;
        }

        return false;
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
            Log::warning('获取IP信息异常：'.$ip);
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

    // 退出
    public function logout(): RedirectResponse
    {
        Auth::logout();

        return Redirect::route('login');
    }

    // 注册
    public function register(Request $request)
    {
        $cacheKey = 'register_times_'.md5(IP::getClientIp()); // 注册限制缓存key

        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'email' => 'required|email|unique:user',
                'password' => 'required|min:6',
                'confirmPassword' => 'required|same:password',
                'term' => 'accepted',
            ], [
                'username.required' => trans('auth.email_null'),
                'email.required' => trans('auth.email_null'),
                'email.email' => trans('auth.email_legitimate'),
                'email.unique' => trans('auth.email_exist'),
                'password.required' => trans('auth.password_null'),
                'password.min' => trans('auth.password_limit'),
                'confirmPassword.required' => trans('auth.confirm_password'),
                'confirmPassword.same' => trans('auth.password_same'),
                'term.accepted' => trans('auth.unaccepted'),
            ]);

            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator->errors());
            }

            $username = $request->input('username');
            $email = $request->input('email');
            $password = $request->input('password');
            $register_token = $request->input('register_token');
            $code = $request->input('code');
            $verify_code = $request->input('verify_code');
            $aff = (int) $request->input('aff');

            // 防止重复提交
            if ($register_token !== Session::get('register_token')) {
                return Redirect::back()->withInput()->withErrors(trans('auth.repeat_request'));
            }

            Session::forget('register_token');

            // 是否开启注册
            if (! sysConfig('is_register')) {
                return Redirect::back()->withErrors(trans('auth.register_close'));
            }

            // 校验域名邮箱黑白名单
            if (sysConfig('is_email_filtering')) {
                $result = $this->emailChecker($email, 1);
                if ($result !== false) {
                    return $result;
                }
            }

            // 如果需要邀请注册
            if (sysConfig('is_invite_register')) {
                // 校验邀请码合法性
                if ($code) {
                    if (Invite::whereCode($code)->whereStatus(0)->doesntExist()) {
                        return Redirect::back()->withInput($request->except('code'))->withErrors(trans('auth.code_error'));
                    }
                } elseif (sysConfig('is_invite_register') == 2) { // 必须使用邀请码
                    return Redirect::back()->withInput()->withErrors(trans('auth.code_null'));
                }
            }

            // 注册前发送激活码
            if (sysConfig('is_activate_account') == 1) {
                if (! $verify_code) {
                    return Redirect::back()->withInput($request->except('verify_code'))->withErrors(trans('auth.captcha_null'));
                }

                $verifyCode = VerifyCode::whereAddress($email)->whereCode($verify_code)->whereStatus(0)->first();
                if (! $verifyCode) {
                    return Redirect::back()->withInput($request->except('verify_code'))->withErrors(trans('auth.captcha_overtime'));
                }

                $verifyCode->status = 1;
                $verifyCode->save();
            }

            // 是否校验验证码
            $captcha = $this->check_captcha($request);
            if ($captcha !== false) {
                return $captcha;
            }

            // 24小时内同IP注册限制
            if (sysConfig('register_ip_limit') && Cache::has($cacheKey)) {
                $registerTimes = Cache::get($cacheKey);
                if ($registerTimes >= sysConfig('register_ip_limit')) {
                    return Redirect::back()->withInput($request->except('code'))->withErrors(trans('auth.register_anti'));
                }
            }

            // 获取可用端口
            $port = Helpers::getPort();
            if ($port > sysConfig('max_port')) {
                return Redirect::back()->withInput()->withErrors(trans('auth.register_close'));
            }

            // 获取aff
            $affArr = $this->getAff($code, $aff);
            $inviter_id = $affArr['inviter_id'];

            $transfer_enable = MB * ((int) sysConfig('default_traffic') + ($inviter_id ? (int) sysConfig('referral_traffic') : 0));

            // 创建新用户
            $uid = Helpers::addUser($email, $password, $transfer_enable, sysConfig('default_days'), $inviter_id);

            // 注册失败，抛出异常
            if (! $uid) {
                return Redirect::back()->withInput()->withErrors(trans('auth.register_fail'));
            }
            // 更新昵称
            User::find($uid)->update(['username' => $username]);

            // 注册次数+1
            if (Cache::has($cacheKey)) {
                Cache::increment($cacheKey);
            } else {
                Cache::put($cacheKey, 1, Day); // 24小时
            }

            // 更新邀请码
            if ($affArr['code_id'] && sysConfig('is_invite_register')) {
                Invite::find($affArr['code_id'])->update(['invitee_id' => $uid, 'status' => 1]);
            }

            // 清除邀请人Cookie
            Cookie::unqueue('register_aff');

            // 注册后发送激活码
            if (sysConfig('is_activate_account') == 2) {
                // 生成激活账号的地址
                $token = $this->addVerifyUrl($uid, $email);
                $activeUserUrl = route('activeAccount', $token);

                $logId = Helpers::addNotificationLog('注册激活', '请求地址：'.$activeUserUrl, 1, $email);
                Mail::to($email)->send(new activeUser($logId, $activeUserUrl));

                Session::flash('successMsg', trans('auth.register_active_tip'));
            } else {
                // 则直接给推荐人加流量
                if ($inviter_id) {
                    $referralUser = User::find($inviter_id);
                    if ($referralUser && $referralUser->expired_at >= date('Y-m-d')) {
                        $referralUser->incrementData(sysConfig('referral_traffic') * MB);
                    }
                }

                if (sysConfig('is_activate_account') == 1) {
                    User::find($uid)->update(['status' => 1]);
                }

                Session::flash('successMsg', trans('auth.register_success'));
            }

            return Redirect::route('login')->withInput();
        }

        $view['emailList'] = sysConfig('is_email_filtering') != 2 ? false : EmailFilter::whereType(2)->get();
        Session::put('register_token', Str::random());

        return view('auth.register', $view);
    }

    //邮箱检查
    private function emailChecker($email, $returnType = 0)
    {
        $emailFilterList = EmailFilter::whereType(sysConfig('is_email_filtering'))->pluck('words')->toArray();
        $emailSuffix = explode('@', $email); // 提取邮箱后缀
        switch (sysConfig('is_email_filtering')) {
            // 黑名单
            case 1:
                if (in_array(strtolower($emailSuffix[1]), $emailFilterList, true)) {
                    if ($returnType) {
                        return Redirect::back()->withErrors(trans('auth.email_banned'));
                    }

                    return Response::json(['status' => 'fail', 'message' => trans('auth.email_banned')]);
                }
                break;
            //白名单
            case 2:
                if (! in_array(strtolower($emailSuffix[1]), $emailFilterList, true)) {
                    if ($returnType) {
                        return Redirect::back()->withErrors(trans('auth.email_invalid'));
                    }

                    return Response::json(['status' => 'fail', 'message' => trans('auth.email_invalid')]);
                }
                break;
            default:
                if ($returnType) {
                    return Redirect::back()->withErrors(trans('auth.email_invalid'));
                }

                return Response::json(['status' => 'fail', 'message' => trans('auth.email_invalid')]);
        }

        return false;
    }

    /**
     * 获取AFF.
     *
     * @param  string|null  $code  邀请码
     * @param  int|null  $aff  URL中的aff参数
     *
     * @return array
     */
    private function getAff($code = null, $aff = null): array
    {
        $data = ['inviter_id' => null, 'code_id' => 0]; // 邀请人ID 与 邀请码ID

        // 有邀请码先用邀请码，用谁的邀请码就给谁返利
        if ($code) {
            $inviteCode = Invite::whereCode($code)->whereStatus(0)->first();
            if ($inviteCode) {
                $data['inviter_id'] = $inviteCode->inviter_id;
                $data['code_id'] = $inviteCode->id;
            }
        }

        // 没有用邀请码或者邀请码是管理员生成的，则检查cookie或者url链接
        if (! $data['inviter_id']) {
            // 检查一下cookie里有没有aff
            $cookieAff = \Request::hasCookie('register_aff');
            if ($cookieAff) {
                $data['inviter_id'] = User::find($cookieAff) ? $cookieAff : null;
            } elseif ($aff) { // 如果cookie里没有aff，就再检查一下请求的url里有没有aff，因为有些人的浏览器会禁用了cookie，比如chrome开了隐私模式
                $data['inviter_id'] = User::find($aff) ? $aff : null;
            }
        }

        return $data;
    }

    // 生成申请的请求地址
    private function addVerifyUrl($uid, $email)
    {
        $token = md5(sysConfig('website_name').$email.microtime());
        $verify = new Verify();
        $verify->user_id = $uid;
        $verify->token = $token;
        $verify->save();

        return $token;
    }

    // 重设密码页
    public function resetPassword(Request $request)
    {
        if ($request->isMethod('POST')) {
            // 校验请求
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ], [
                'email.required' => trans('auth.email_null'),
                'email.email' => trans('auth.email_legitimate'),
            ]);

            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator->errors());
            }

            $email = $request->input('email');

            // 是否开启重设密码
            if (! sysConfig('is_reset_password')) {
                return Redirect::back()->withErrors(trans('auth.reset_password_close', ['email' => sysConfig('webmaster_email')]));
            }

            // 查找账号
            $user = User::whereEmail($email)->first();
            if (! $user) {
                return Redirect::back()->withErrors(trans('auth.email_notExist'));
            }

            // 24小时内重设密码次数限制
            $resetTimes = 0;
            if (Cache::has('resetPassword_'.md5($email))) {
                $resetTimes = Cache::get('resetPassword_'.md5($email));
                if ($resetTimes >= sysConfig('reset_password_times')) {
                    return Redirect::back()->withErrors(trans('auth.reset_password_limit', ['time' => sysConfig('reset_password_times')]));
                }
            }

            // 生成取回密码的地址
            $token = $this->addVerifyUrl($user->id, $email);

            // 发送邮件
            $resetPasswordUrl = route('resettingPasswd', $token);

            $logId = Helpers::addNotificationLog('重置密码', '请求地址：'.$resetPasswordUrl, 1, $email);
            Mail::to($email)->send(new resetPassword($logId, $resetPasswordUrl));

            Cache::put('resetPassword_'.md5($email), $resetTimes + 1, Day);

            return Redirect::back()->with('successMsg', trans('auth.reset_password_success_tip'));
        }

        return view('auth.resetPassword');
    }

    // 重设密码
    public function reset(Request $request, $token)
    {
        if (! $token) {
            return Redirect::route('login');
        }

        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6',
                'confirmPassword' => 'required|same:password',
            ], [
                'password.required' => trans('auth.password_null'),
                'password.min' => trans('auth.password_limit'),
                'confirmPassword.required' => trans('auth.password_null'),
                'confirmPassword.min' => trans('auth.password_limit'),
                'confirmPassword.same' => trans('auth.password_same'),
            ]);

            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator->errors());
            }

            $password = $request->input('password');
            // 校验账号
            $verify = Verify::type(1)->whereToken($token)->first();
            $user = $verify->user;
            if (! $verify) {
                return Redirect::route('login');
            }

            if ($verify->status === 1) {
                return Redirect::back()->withErrors(trans('auth.overtime'));
            }

            if ($user->status < 0) {
                return Redirect::back()->withErrors(trans('auth.email_banned'));
            }

            if (Hash::check($password, $verify->user->password)) {
                return Redirect::back()->withErrors(trans('auth.reset_password_same_fail'));
            }

            // 更新密码
            if (! $user->update(['password' => $password])) {
                return Redirect::back()->withErrors(trans('auth.reset_password_fail'));
            }

            // 置为已使用
            $verify->status = 1;
            $verify->save();

            return Redirect::route('login')->with('successMsg', trans('auth.reset_password_new'));
        }

        $verify = Verify::type(1)->whereToken($token)->first();
        if (! $verify) {
            return Redirect::route('login');
        }

        if (time() - strtotime($verify->created_at) >= 1800) {
            // 置为已失效
            $verify->status = 2;
            $verify->save();
        }

        // 重新获取一遍verify
        $view['verify'] = Verify::type(1)->whereToken($token)->first();

        return view('auth.reset', $view);
    }

    // 激活账号页
    public function activeUser(Request $request)
    {
        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:user,email',
            ], [
                'email.required' => trans('auth.email_null'),
                'email.email' => trans('auth.email_legitimate'),
                'email.exists' => trans('auth.email_notExist'),
            ]);

            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator->errors());
            }

            $email = $request->input('email');

            // 是否开启账号激活
            if (sysConfig('is_activate_account') != 2) {
                return Redirect::back()->withInput()->withErrors(trans('auth.active_close', ['email' => sysConfig('webmaster_email')]));
            }

            // 查找账号
            $user = User::whereEmail($email)->firstOrFail();
            if ($user->status < 0) {
                return Redirect::back()->withErrors(trans('auth.login_ban', ['email' => sysConfig('webmaster_email')]));
            }

            if ($user->status > 0) {
                return Redirect::back()->withErrors(trans('auth.email_normal'));
            }

            // 24小时内激活次数限制
            $activeTimes = 0;
            if (Cache::has('activeUser_'.md5($email))) {
                $activeTimes = Cache::get('activeUser_'.md5($email));
                if ($activeTimes >= sysConfig('active_times')) {
                    return Redirect::back()->withErrors(trans('auth.active_limit', ['time' => sysConfig('webmaster_email')]));
                }
            }

            // 生成激活账号的地址
            $token = $this->addVerifyUrl($user->id, $email);

            // 发送邮件
            $activeUserUrl = route('activeAccount', $token);

            $logId = Helpers::addNotificationLog('激活账号', '请求地址：'.$activeUserUrl, 1, $email);
            Mail::to($email)->send(new activeUser($logId, $activeUserUrl));

            Cache::put('activeUser_'.md5($email), $activeTimes + 1, Day);

            return Redirect::back()->with('successMsg', trans('auth.register_active_tip'));
        }

        return view('auth.activeUser');
    }

    // 激活账号
    public function active($token)
    {
        if (! $token) {
            return Redirect::route('login');
        }

        $verify = Verify::type(1)->with('user')->whereToken($token)->first();
        $user = $verify->user;
        if (! $verify) {
            return Redirect::route('login');
        }

        if (empty($user) || $verify->status > 0) {
            Session::flash('errorMsg', trans('auth.overtime'));

            return view('auth.active');
        }

        if ($user->status !== 0) {
            Session::flash('errorMsg', trans('auth.email_normal'));

            return view('auth.active');
        }

        if (time() - strtotime($verify->created_at) >= 1800) {
            Session::flash('errorMsg', trans('auth.overtime'));

            // 置为已失效
            $verify->status = 2;
            $verify->save();

            return view('auth.active');
        }

        // 更新账号状态
        if (! $user->update(['status' => 1])) {
            Session::flash('errorMsg', trans('auth.active_fail'));

            return Redirect::back();
        }

        // 置为已使用
        $verify->status = 1;
        $verify->save();

        // 账号激活后给邀请人送流量
        $inviter = $user->inviter;
        if ($inviter) {
            $inviter->incrementData(sysConfig('referral_traffic') * MB);
        }

        Session::flash('successMsg', trans('auth.active_success'));

        return view('auth.active');
    }

    // 发送注册验证码
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:user',
        ], [
            'email.required' => trans('auth.email_null'),
            'email.email' => trans('auth.email_legitimate'),
            'email.unique' => trans('auth.email_exist'),
        ]);

        $email = $request->input('email');

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->getMessageBag()->first()]);
        }
        $ip = IP::getClientIP();

        // 校验域名邮箱黑白名单
        if (sysConfig('is_email_filtering')) {
            $result = $this->emailChecker($email);
            if ($result !== false) {
                return $result;
            }
        }

        // 是否开启注册发送验证码
        if ((int) sysConfig('is_activate_account') !== 1) {
            return Response::json(['status' => 'fail', 'message' => trans('auth.captcha_close')]);
        }

        // 防刷机制
        if (Cache::has('send_verify_code_'.md5($ip))) {
            return Response::json(['status' => 'fail', 'message' => trans('auth.register_anti')]);
        }

        // 发送邮件
        $code = Str::random(6);
        $logId = Helpers::addNotificationLog('发送注册验证码', '验证码：'.$code, 1, $email);
        Mail::to($email)->send(new sendVerifyCode($logId, $code));

        $this->addVerifyCode($email, $code);

        Cache::put('send_verify_code_'.md5($ip), $ip, Minute);

        return Response::json(['status' => 'success', 'message' => trans('auth.captcha_send')]);
    }

    // 生成注册验证码
    private function addVerifyCode(string $email, string $code): void
    {
        $verify = new VerifyCode();
        $verify->address = $email;
        $verify->code = $code;
        $verify->save();
    }

    // 公开的邀请码列表
    public function free()
    {
        $view['inviteList'] = Invite::whereInviterId(null)->whereStatus(0)->paginate();

        return view('auth.free', $view);
    }

    // 切换语言
    public function switchLang($locale): RedirectResponse
    {
        Session::put('locale', $locale);

        return Redirect::back();
    }
}
