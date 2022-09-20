<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\IP;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\EmailFilter;
use App\Models\Invite;
use App\Models\User;
use App\Models\Verify;
use App\Models\VerifyCode;
use App\Notifications\AccountActivation;
use App\Notifications\PasswordReset;
use App\Notifications\Verification;
use Auth;
use Cache;
use Captcha;
use Cookie;
use Hash;
use Hashids\Hashids;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Notification;
use Redirect;
use Response;
use Session;
use Str;
use Validator;

class AuthController extends Controller
{
    // 登录
    public function showLoginForm()
    {
        // 根据权限跳转
        if (Auth::check()) {
            if (Auth::getUser()->can('admin.index')) {
                return Redirect::route('admin.index');
            }

            return Redirect::route('home');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        // 是否校验验证码
        $captcha = $this->check_captcha($request);
        if ($captcha !== false) {
            return $captcha;
        }

        // 验证账号并创建会话
        if (! Auth::attempt($data, $request->has('remember'))) {
            return Redirect::back()->withInput()->withErrors(trans('auth.error.login_failed'));
        }
        $user = Auth::getUser();

        if (! $user) {
            return Redirect::back()->withInput()->withErrors(trans('auth.error.login_error'));
        }

        if ($request->routeIs('admin.login.post') && $user->cannot('admin.index')) {
            // 管理页面登录
            // 非权限者清场
            Auth::logout();

            return Redirect::route('login');
        }

        // 校验普通用户账号状态
        if ($user->status === -1) {
            Auth::logout(); // 强制销毁会话，因为Auth::attempt的时候会产生会话

            return Redirect::back()->withInput()->withErrors(trans('auth.error.account_baned'));
        }

        if ($user->status === 0 && sysConfig('is_activate_account')) {
            Auth::logout(); // 强制销毁会话，因为Auth::attempt的时候会产生会话

            return Redirect::back()->withInput()->withErrors(trans('auth.active.promotion.0').'<a href="'.route('active').'?username='.$user->username.
                '" target="_blank">👉【'.trans('common.active_item', ['attribute' => trans('common.account')]).'】👈</span></a><br>'.trans('auth.active.promotion.1'));
        }

        Helpers::userLoginAction($user, IP::getClientIp()); // 用户登录后操作

        return redirect()->back();
    }

    // 校验验证码
    private function check_captcha(Request $request)
    {
        switch (sysConfig('is_captcha')) {
            case 1: // 默认图形验证码
                if (! Captcha::check($request->input('captcha'))) {
                    return Redirect::back()->withInput()->withErrors(trans('auth.captcha.error.failed'));
                }
                break;
            case 2: // Geetest
                $validator = Validator::make($request->all(), [
                    'geetest_challenge' => 'required|geetest',
                ]);

                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors(trans('auth.captcha.error.failed'));
                }
                break;
            case 3: // Google reCAPTCHA
                $validator = Validator::make($request->all(), [
                    'g-recaptcha-response' => 'required|NoCaptcha',
                ]);

                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors(trans('auth.captcha.error.failed'));
                }
                break;
            case 4: // hCaptcha
                $validator = Validator::make($request->all(), [
                    'h-captcha-response' => 'required|HCaptcha',
                ]);

                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors(trans('auth.captcha.error.failed'));
                }
                break;
            default: // 不启用验证码
                break;
        }

        return false;
    }

    // 退出
    public function logout(): RedirectResponse
    {
        Auth::logout();

        return Redirect::route('login');
    }

    public function showRegistrationForm()
    {
        Session::put('register_token', Str::random());

        return view('auth.register', ['emailList' => (int) sysConfig('is_email_filtering') !== 2 ? false : EmailFilter::whereType(2)->get()]);
    }

    // 注册
    public function register(RegisterRequest $request)
    {
        $cacheKey = 'register_times_'.md5(IP::getClientIp()); // 注册限制缓存key

        $data = $request->validated();
        $register_token = $request->input('register_token');
        $invite_code = $request->input('code');
        $verify_code = $request->input('verify_code');
        $aff = $request->input('aff');

        // 防止重复提交
        if ($register_token !== Session::get('register_token')) {
            return Redirect::back()->withInput()->withErrors(trans('auth.error.repeat_request'));
        }

        Session::forget('register_token');

        // 是否开启注册
        if (! sysConfig('is_register')) {
            return Redirect::back()->withErrors(trans('auth.register.error.disable'));
        }

        // 校验域名邮箱黑白名单
        if (sysConfig('is_email_filtering')) {
            $result = $this->emailChecker($data['username'], 1);
            if ($result !== false) {
                return $result;
            }
        }

        // 如果需要邀请注册
        if (sysConfig('is_invite_register')) {
            // 校验邀请码合法性
            if ($invite_code) {
                if (Invite::whereCode($invite_code)->whereStatus(0)->doesntExist()) {
                    return Redirect::back()->withInput($request->except('code'))->withErrors(trans('auth.invite.error.unavailable'));
                }
            } elseif ((int) sysConfig('is_invite_register') === 2) { // 必须使用邀请码
                return Redirect::back()->withInput()->withErrors(trans('validation.required', ['attribute' => trans('auth.invite.attribute')]));
            }
        }

        // 注册前发送激活码
        if ((int) sysConfig('is_activate_account') === 1) {
            if (! $verify_code) {
                return Redirect::back()->withInput($request->except('verify_code'))->withErrors(trans('auth.captcha.required'));
            }

            $verifyCode = VerifyCode::whereAddress($data['username'])->whereCode($verify_code)->whereStatus(0)->first();
            if (! $verifyCode) {
                return Redirect::back()->withInput($request->except('verify_code'))->withErrors(trans('auth.captcha.error.timeout'));
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
                return Redirect::back()->withInput($request->except('code'))->withErrors(trans('auth.register.error.throttle'));
            }
        }

        // 获取可用端口
        $port = Helpers::getPort();
        if ($port > sysConfig('max_port')) {
            return Redirect::back()->withInput()->withErrors(trans('auth.register.error.disable'));
        }

        // 获取aff
        $affArr = $this->getAff($invite_code, $aff);
        $inviter_id = $affArr['inviter_id'];

        $transfer_enable = MB * ((int) sysConfig('default_traffic') + ($inviter_id ? (int) sysConfig('referral_traffic') : 0));

        // 创建新用户
        $user = Helpers::addUser($data['username'], $data['password'], $transfer_enable, sysConfig('default_days'), $inviter_id, $data['nickname']);

        // 注册失败，抛出异常
        if (! $user) {
            return Redirect::back()->withInput()->withErrors(trans('auth.register.failed'));
        }

        // 注册次数+1
        if (Cache::has($cacheKey)) {
            Cache::increment($cacheKey);
        } else {
            Cache::put($cacheKey, 1, Day); // 24小时
        }

        // 更新邀请码
        if ($affArr['code_id'] && sysConfig('is_invite_register')) {
            $invite = Invite::find($affArr['code_id']);
            if ($invite) {
                $invite->update(['invitee_id' => $user->id, 'status' => 1]);
            }
        }

        // 清除邀请人Cookie
        Cookie::unqueue('register_aff');

        // 注册后发送激活码
        if ((int) sysConfig('is_activate_account') === 2) {
            // 生成激活账号的地址
            $token = $this->addVerifyUrl($user->id, $user->username);
            $activeUserUrl = route('activeAccount', $token);

            $user->notifyNow(new AccountActivation($activeUserUrl));

            Session::flash('successMsg',
                __("Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another."));
        } else {
            // 则直接给推荐人加流量
            if ($inviter_id) {
                $referralUser = User::find($inviter_id);
                if ($referralUser && $referralUser->expired_at >= date('Y-m-d')) {
                    $referralUser->incrementData(sysConfig('referral_traffic') * MB);
                }
            }

            if ((int) sysConfig('is_activate_account') === 1) {
                $user->update(['status' => 1]);
            }

            Session::flash('successMsg', trans('auth.register.success'));
        }

        return Redirect::route('login')->withInput();
    }

    //邮箱检查
    private function emailChecker($email, $returnType = 0)
    {
        $emailFilterList = EmailFilter::whereType(sysConfig('is_email_filtering'))->pluck('words')->toArray();
        $emailSuffix = explode('@', $email); // 提取邮箱后缀

        if ($emailSuffix) {
            switch (sysConfig('is_email_filtering')) {
                // 黑名单
                case 1:
                    if (in_array(strtolower($emailSuffix[1]), $emailFilterList, true)) {
                        if ($returnType) {
                            return Redirect::back()->withErrors(trans('auth.email.error.banned'));
                        }

                        return Response::json(['status' => 'fail', 'message' => trans('auth.email.error.banned')]);
                    }
                    break;
                //白名单
                case 2:
                    if (! in_array(strtolower($emailSuffix[1]), $emailFilterList, true)) {
                        if ($returnType) {
                            return Redirect::back()->withErrors(trans('auth.email.error.invalid'));
                        }

                        return Response::json(['status' => 'fail', 'message' => trans('auth.email.error.invalid')]);
                    }
                    break;
                default:
                    if ($returnType) {
                        return Redirect::back()->withErrors(trans('auth.email.error.invalid'));
                    }

                    return Response::json(['status' => 'fail', 'message' => trans('auth.email.error.invalid')]);
            }
        }

        return false;
    }

    /**
     * 获取AFF.
     *
     * @param  string|null  $code  邀请码
     * @param  int|null  $aff  URL中的aff参数
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
            $cookieAff = \request()->cookie('register_aff');
            if ($cookieAff) {
                $cookieAff = $this->affConvert($cookieAff);
                $data['inviter_id'] = $cookieAff && User::find($cookieAff) ? $cookieAff : null;
            } elseif ($aff) { // 如果cookie里没有aff，就再检查一下请求的url里有没有aff，因为有些人的浏览器会禁用了cookie，比如chrome开了隐私模式
                $aff = $this->affConvert($aff);
                $data['inviter_id'] = $aff && User::find($aff) ? $aff : null;
            }
        }

        return $data;
    }

    private function affConvert($aff)
    {
        if (is_numeric($aff)) {
            return $aff;
        }

        $decode = (new Hashids(sysConfig('aff_salt'), 8))->decode($aff);
        if ($decode) {
            return $decode[0];
        }

        return false;
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
            $validator = Validator::make($request->all(), ['username' => 'required|'.(sysConfig('username_type') ?? 'email').'|exists:user,username']);

            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator->errors());
            }

            $username = $request->input('username');

            // 是否开启重设密码
            if (! sysConfig('password_reset_notification')) {
                return Redirect::back()->withErrors(trans('auth.password.reset.error.disabled', ['email' => sysConfig('webmaster_email')]));
            }

            // 查找账号
            $user = User::whereUsername($username)->firstOrFail();

            // 24小时内重设密码次数限制
            $resetTimes = 0;
            if (Cache::has('resetPassword_'.md5($username))) {
                $resetTimes = Cache::get('resetPassword_'.md5($username));
                if ($resetTimes >= sysConfig('reset_password_times')) {
                    return Redirect::back()->withErrors(trans('auth.password.reset.error.throttle', ['time' => sysConfig('reset_password_times')]));
                }
            }

            // 生成取回密码的地址
            $token = $this->addVerifyUrl($user->id, $username);

            // 发送邮件
            $resetUrl = route('resettingPasswd', $token);
            $user->notifyNow(new PasswordReset($resetUrl));

            Cache::put('resetPassword_'.md5($username), $resetTimes + 1, Day);

            return Redirect::back()->with('successMsg', trans('auth.password.reset.sent'));
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
                'password' => 'required|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator->errors());
            }

            $password = $request->input('password');
            // 校验账号
            $verify = Verify::type(1)->whereToken($token)->firstOrFail();
            $user = $verify->user;
            if (! $verify) {
                return Redirect::route('login');
            }

            if ($user->status === -1) {
                return Redirect::back()->withErrors(trans('auth.error.account_baned'));
            }

            if ($verify->status === 1) {
                return Redirect::back()->withErrors(trans('auth.error.url_timeout'));
            }

            if (Hash::check($password, $verify->user->password)) {
                return Redirect::back()->withErrors(trans('auth.password.reset.error.same'));
            }

            // 更新密码
            if (! $user->update(['password' => $password])) {
                return Redirect::back()->withErrors(trans('auth.password.reset.error.failed'));
            }

            // 置为已使用
            $verify->status = 1;
            $verify->save();

            return Redirect::route('login')->with('successMsg', trans('auth.password.reset.success'));
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

        return view('auth.reset', ['verify' => Verify::type(1)->whereToken($token)->first()]); // 重新获取一遍verify
    }

    // 激活账号页
    public function activeUser(Request $request)
    {
        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), ['username' => 'required|'.(sysConfig('username_type') ?? 'email').'|exists:user,username']);

            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator->errors());
            }

            $username = $request->input('username');

            // 是否开启账号激活
            if (! sysConfig('is_activate_account')) {
                return Redirect::back()->withInput()->withErrors(trans('auth.active.error.disable'));
            }

            // 查找账号
            $user = User::whereUsername($username)->firstOrFail();
            if ($user->status === -1) {
                return Redirect::back()->withErrors(trans('auth.error.account_baned'));
            }

            if ($user->status === 1) {
                return Redirect::back()->withErrors(trans('auth.active.error.activated'));
            }

            // 24小时内激活次数限制
            $activeTimes = 0;
            if (Cache::has('activeUser_'.md5($username))) {
                $activeTimes = Cache::get('activeUser_'.md5($username));
                if ($activeTimes >= sysConfig('active_times')) {
                    return Redirect::back()->withErrors(trans('auth.active.error.throttle', ['email' => sysConfig('webmaster_email')]));
                }
            }

            // 生成激活账号的地址
            $token = $this->addVerifyUrl($user->id, $username);

            // 发送邮件
            $activeUserUrl = route('activeAccount', $token);

            Notification::route('mail', $username)->notifyNow(new AccountActivation($activeUserUrl));

            Cache::put('activeUser_'.md5($username), $activeTimes + 1, Day);

            return Redirect::back()->with('successMsg', trans('auth.active.sent'));
        }

        return view('auth.activeUser');
    }

    // 激活账号
    public function active($token)
    {
        if (! $token) {
            return Redirect::route('login');
        }

        $verify = Verify::type(1)->with('user')->whereToken($token)->firstOrFail();
        $user = $verify->user;
        if (! $verify) {
            return Redirect::route('login');
        }

        if (empty($user) || $verify->status > 0) {
            Session::flash('errorMsg', trans('auth.error.url_timeout'));

            return view('auth.active');
        }

        if ($user->status === 1) {
            Session::flash('errorMsg', trans('auth.active.error.activated'));

            return view('auth.active');
        }

        if (time() - strtotime($verify->created_at) >= 1800) {
            Session::flash('errorMsg', trans('auth.error.url_timeout'));

            // 置为已失效
            $verify->status = 2;
            $verify->save();

            return view('auth.active');
        }

        // 更新账号状态
        if (! $user->update(['status' => 1])) {
            Session::flash('errorMsg', trans('common.active_item', ['attribute' => trans('common.failed')]));

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

        Session::flash('successMsg', trans('common.active_item', ['attribute' => trans('common.success')]));

        return view('auth.active');
    }

    // 发送注册验证码
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), ['username' => 'required|'.(sysConfig('username_type') ?? 'email').'|unique:user,username']);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->getMessageBag()->first()]);
        }
        $email = $request->input('username');
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
            return Response::json(['status' => 'fail', 'message' => trans('auth.active.error.disable')]);
        }

        // 防刷机制
        if (Cache::has('send_verify_code_'.md5($ip))) {
            return Response::json(['status' => 'fail', 'message' => trans('auth.register.error.throttle')]);
        }

        // 发送邮件
        $code = Str::random(6);
        if (VerifyCode::create(['address' => $email, 'code' => $code])) { // 生成注册验证码
            Notification::route('mail', $email)->notifyNow(new Verification($code));
        }

        Cache::put('send_verify_code_'.md5($ip), $ip, Minute);

        return Response::json(['status' => 'success', 'message' => trans('auth.captcha.sent')]);
    }

    // 公开的邀请码列表
    public function free()
    {
        return view('auth.free', ['inviteList' => Invite::whereInviterId(null)->whereStatus(0)->paginate()]);
    }

    // 切换语言
    public function switchLang(string $locale): RedirectResponse
    {
        Session::put('locale', $locale);

        return Redirect::back();
    }
}
