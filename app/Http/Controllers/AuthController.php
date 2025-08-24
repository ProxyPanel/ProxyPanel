<?php

namespace App\Http\Controllers;

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
use App\Utils\Helpers;
use App\Utils\IP;
use Hash;
use Hashids\Hashids;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Notification;
use romanzipp\Turnstile\Rules\TurnstileCaptcha;
use Str;
use Validator;

class AuthController extends Controller
{
    // 登录
    public function showLoginForm(): RedirectResponse|View
    {
        // 根据权限跳转
        if (auth()->check()) {
            if (auth()->getUser()?->can('admin.index')) {
                return redirect()->route('admin.index');
            }

            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // 是否校验验证码
        $captcha = $this->check_captcha($request);
        if ($captcha !== true) {
            return $captcha;
        }

        // 验证账号并创建会话
        if (! auth()->attempt($data, $request->has('remember'))) {
            return redirect()->back()->withInput()->withErrors(trans('auth.error.login_failed'));
        }

        $user = auth()->getUser();
        if (! $user) {
            return redirect()->back()->withInput()->withErrors(trans('auth.error.login_error'));
        }

        if ($user->can('admin.index')) {
            return redirect()->back();
        }

        if ($request->routeIs('admin.login.post')) {
            // 管理页面登录, 非权限者清场
            auth()->logout();

            return redirect()->route('login')->withErrors(trans('common.failed_item', ['attribute' => trans('auth.login')]));
        }

        // 校验普通用户账号状态
        if ($user->status === -1) {
            auth()->logout(); // 强制销毁会话，因为Auth::attempt的时候会产生会话

            return redirect()->back()->withInput()->withErrors(trans('auth.error.account_baned'));
        }

        if ($user->status === 0 && sysConfig('is_activate_account')) {
            auth()->logout(); // 强制销毁会话，因为Auth::attempt的时候会产生会话

            return redirect()->back()->withInput()->withErrors(trans('auth.active.promotion',
                ['action' => '<a href="'.route('active', ['username' => $user->username]).'" target="_blank">'.trans('common.active_item', ['attribute' => trans('common.account')]).'</a>']));
        }

        Helpers::userLoginAction($user, IP::getClientIp()); // 用户登录后操作

        return redirect()->back();
    }

    private function check_captcha(Request $request): RedirectResponse|bool
    {
        // Define the rules based on the captcha type
        $rules = [
            1 => ['captcha' => 'required|captcha'], // Mews\Captcha
            2 => ['geetest_challenge' => 'required|geetest'], // Geetest
            3 => ['g-recaptcha-response' => 'required|NoCaptcha'], // Google reCAPTCHA
            4 => ['h-captcha-response' => 'required|HCaptcha'], // hCaptcha
            5 => ['cf-turnstile-response' => ['required', 'string', new TurnstileCaptcha]], // Turnstile
        ];

        // Get the current captcha setting
        $captchaType = sysConfig('is_captcha');

        // Check if the captcha is enabled and has a defined rule
        if (isset($rules[$captchaType])) {
            $validator = Validator::make($request->all(), $rules[$captchaType]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors(trans('auth.captcha.error.failed'));
            }
        }

        return true;
    }

    public function logout(Request $request): RedirectResponse
    { // 退出
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showRegistrationForm(): View
    {
        session()->put('register_token', Str::random());

        return view('auth.register', ['emailList' => sysConfig('is_email_filtering') === '2' ? EmailFilter::whereType(2)->get() : false]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    { // 注册
        $cacheKey = 'register_times_'.md5(IP::getClientIp()); // 注册限制缓存key

        $data = $request->validated();
        $register_token = $request->input('register_token');
        $invite_code = $request->input('code');
        $verify_code = $request->input('verify_code');
        $aff = $request->input('aff');

        // 防止重复提交
        if ($register_token !== session()->pull('register_token')) {
            return redirect()->back()->withInput()->withErrors(trans('auth.error.repeat_request'));
        }

        // 是否开启注册
        if (! sysConfig('is_register')) {
            return redirect()->back()->withErrors(trans('auth.register.error.disable'));
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
                    return redirect()->back()->withInput($request->except('code'))->withErrors(trans('auth.invite.unavailable'));
                }
            } elseif ((int) sysConfig('is_invite_register') === 2) { // 必须使用邀请码
                return redirect()->back()->withInput()->withErrors(trans('validation.required', ['attribute' => trans('user.invite.attribute')]));
            }
        }

        // 注册前发送激活码
        if ((int) sysConfig('is_activate_account') === 1) {
            if (! $verify_code) {
                return redirect()->back()->withInput($request->except('verify_code'))->withErrors(trans('auth.captcha.required'));
            }

            $verifyCode = VerifyCode::whereAddress($data['username'])->whereCode($verify_code)->whereStatus(0)->first();
            if (! $verifyCode) {
                return redirect()->back()->withInput($request->except('verify_code'))->withErrors(trans('auth.captcha.error.timeout'));
            }

            $verifyCode->update(['status' => 1]);
        }

        // 是否校验验证码
        $captcha = $this->check_captcha($request);
        if ($captcha !== true) {
            return $captcha;
        }

        // 24小时内同IP注册限制
        if (sysConfig('register_ip_limit') && cache()->has($cacheKey)) {
            $registerTimes = cache()->get($cacheKey);
            if ($registerTimes >= sysConfig('register_ip_limit')) {
                return redirect()->back()->withInput($request->except('code'))->withErrors(trans('auth.register.error.throttle'));
            }
        }

        // 获取可用端口
        $port = Helpers::getPort();
        if ($port > sysConfig('max_port')) {
            return redirect()->back()->withInput()->withErrors(trans('auth.register.error.disable'));
        }

        // 获取aff
        $affArr = $this->getAff($invite_code, $aff);
        $inviter_id = $affArr['inviter_id'];

        $transfer_enable = MiB * ((int) sysConfig('default_traffic') + ($inviter_id ? (int) sysConfig('referral_traffic') : 0));

        // 创建新用户
        if (! $user = Helpers::addUser($data['username'], $data['password'], $transfer_enable, (int) sysConfig('default_days'), $inviter_id, $data['nickname'])) { // 注册失败，抛出异常
            return redirect()->back()->withInput()->withErrors(trans('auth.register.failed'));
        }

        // 注册次数+1
        if (cache()->has($cacheKey)) {
            cache()->increment($cacheKey);
        } else {
            cache()->put($cacheKey, 1, Day); // 24小时
        }

        // 更新邀请码
        if ($affArr['code_id'] && sysConfig('is_invite_register')) {
            Invite::find($affArr['code_id'])?->update(['invitee_id' => $user->id, 'status' => 1]);
        }

        // 清除邀请人Cookie
        cookie()->unqueue('register_aff');

        // 注册后发送激活码
        if ((int) sysConfig('is_activate_account') === 2) {
            // 生成激活账号的地址
            $token = $this->addVerifyUrl($user->id, $user->username);
            $activeUserUrl = route('activeAccount', $token);

            $user->notifyNow(new AccountActivation($activeUserUrl));

            session()->flash('successMsg',
                __("Thank you for signing up! Before you start, you need to verify your email by clicking on the link we have just sent to your email! If you haven't received an email, we would be happy to send another one."));
        } else {
            // 则直接给推荐人加流量
            if ($inviter_id) {
                $referralUser = User::find($inviter_id);
                if ($referralUser && $referralUser->expiration_date >= date('Y-m-d')) {
                    $referralUser->incrementData(sysConfig('referral_traffic') * MiB);
                }
            }

            if ((int) sysConfig('is_activate_account') === 1) {
                $user->update(['status' => 1]);
            }

            session()->flash('successMsg', trans('common.success_item', ['attribute' => trans('auth.register.attribute')]));
        }

        return redirect()->route('login')->withInput();
    }

    private function emailChecker(string $email, int $returnType = 0): RedirectResponse|JsonResponse|false
    { // 邮箱检查
        $emailFilterList = EmailFilter::whereType(sysConfig('is_email_filtering'))->pluck('words')->toArray();
        $emailSuffix = explode('@', $email); // 提取邮箱后缀

        if ($emailSuffix) {
            switch (sysConfig('is_email_filtering')) {
                case 1: // 黑名单
                    if (in_array(strtolower($emailSuffix[1]), $emailFilterList, true)) {
                        if ($returnType) {
                            return redirect()->back()->withErrors(trans('auth.email.error.banned'));
                        }

                        return response()->json(['status' => 'fail', 'message' => trans('auth.email.error.banned')]);
                    }
                    break;
                case 2: // 白名单
                    if (! in_array(strtolower($emailSuffix[1]), $emailFilterList, true)) {
                        if ($returnType) {
                            return redirect()->back()->withErrors(trans('auth.email.error.invalid'));
                        }

                        return response()->json(['status' => 'fail', 'message' => trans('auth.email.error.invalid')]);
                    }
                    break;
                default:
                    if ($returnType) {
                        return redirect()->back()->withErrors(trans('auth.email.error.invalid'));
                    }

                    return response()->json(['status' => 'fail', 'message' => trans('auth.email.error.invalid')]);
            }
        }

        return false;
    }

    private function getAff(?string $code, string|int|null $aff): array
    { // 获取AFF
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
            $cookieAff = \request()?->cookie('register_aff'); // 检查一下cookie里有没有aff
            if ($cookieAff || $aff) {
                $data['inviter_id'] = $this->setInviter($aff ?: $cookieAff);
            }
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

    private function addVerifyUrl(int $uid, string $email): string
    { // 生成申请的请求地址
        return Verify::create([
            'user_id' => $uid,
            'token' => md5(sysConfig('website_name').$email.microtime()),
        ])->token;
    }

    public function resetPassword(Request $request): RedirectResponse|View
    { // 重设密码页
        if ($request->isMethod('POST')) {
            // 校验请求
            $validator = Validator::make($request->all(), ['username' => 'required|'.(sysConfig('username_type') ?? 'email').'|exists:user,username']);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $username = $request->input('username');

            // 是否开启重设密码
            if (! sysConfig('password_reset_notification')) {
                return redirect()->back()->withErrors(trans('auth.password.reset.error.disabled'));
            }

            // 查找账号
            $user = User::whereUsername($username)->firstOrFail();

            // 24小时内重设密码次数限制
            $resetTimes = 0;
            if (cache()->has('resetPassword_'.md5($username))) {
                $resetTimes = cache()->get('resetPassword_'.md5($username));
                if ($resetTimes >= sysConfig('reset_password_times')) {
                    return redirect()->back()->withErrors(trans('auth.password.reset.error.throttle', ['time' => sysConfig('reset_password_times')]));
                }
            }

            // 生成取回密码的地址
            $token = $this->addVerifyUrl($user->id, $username);

            // 发送邮件
            $resetUrl = route('resettingPasswd', $token);
            $user->notifyNow(new PasswordReset($resetUrl));

            cache()->put('resetPassword_'.md5($username), $resetTimes + 1, Day);

            return redirect()->back()->with('successMsg', trans('auth.password.reset.sent'));
        }

        return view('auth.resetPassword');
    }

    public function reset(Request $request, ?string $token): RedirectResponse|View
    { // 重设密码
        if (! $token) {
            return redirect()->route('login');
        }

        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $password = $request->input('password');
            // 校验账号
            $verify = Verify::type(1)->whereToken($token)->firstOrFail();
            $user = $verify->user;
            if (! $verify) {
                return redirect()->route('login');
            }

            if ($user->status === -1) {
                return redirect()->back()->withErrors(trans('auth.error.account_baned'));
            }

            if ($verify->status === 1) {
                return redirect()->back()->withErrors(trans('auth.error.url_timeout'));
            }

            if (Hash::check($password, $verify->user->password)) {
                return redirect()->back()->withErrors(trans('auth.password.reset.error.same'));
            }

            // 更新密码
            if (! $user->update(['password' => $password])) {
                return redirect()->back()->withErrors(trans('common.failed_item', ['attribute' => trans('auth.password.reset.attribute')]));
            }

            // 置为已使用
            $verify->update(['status' => 1]);

            return redirect()->route('login')->with('successMsg', trans('auth.password.reset.success'));
        }

        $verify = Verify::type(1)->whereToken($token)->first();
        if (! $verify) {
            return redirect()->route('login');
        }

        if (time() - strtotime($verify->created_at) >= 1800) {
            // 置为已失效
            $verify->update(['status' => 2]);
        }

        return view('auth.reset', ['verify' => Verify::type(1)->whereToken($token)->first()]); // 重新获取一遍verify
    }

    public function activeUser(Request $request): RedirectResponse|View
    { // 激活账号页
        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), ['username' => 'required|'.(sysConfig('username_type') ?? 'email').'|exists:user,username']);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $username = $request->input('username');

            // 是否开启账号激活
            if (! sysConfig('is_activate_account')) {
                return redirect()->back()->withInput()->withErrors(trans('auth.active.error.disable'));
            }

            // 查找账号
            $user = User::whereUsername($username)->firstOrFail();
            if ($user->status === -1) {
                return redirect()->back()->withErrors(trans('auth.error.account_baned'));
            }

            if ($user->status === 1) {
                return redirect()->back()->withErrors(trans('auth.active.error.activated'));
            }

            // 24小时内激活次数限制
            $activeTimes = 0;
            if (cache()->has('activeUser_'.md5($username))) {
                $activeTimes = cache()->get('activeUser_'.md5($username));
                if ($activeTimes >= sysConfig('active_times')) {
                    return redirect()->back()->withErrors(trans('auth.active.error.throttle'));
                }
            }

            // 生成激活账号的地址
            $token = $this->addVerifyUrl($user->id, $username);

            // 发送邮件
            $activeUserUrl = route('activeAccount', $token);

            Notification::route('mail', $username)->notifyNow(new AccountActivation($activeUserUrl));

            cache()->put('activeUser_'.md5($username), $activeTimes + 1, Day);

            return redirect()->back()->with('successMsg', trans('auth.active.sent'));
        }

        return view('auth.activeUser');
    }

    public function active(string $token): RedirectResponse|View
    { // 激活账号
        $verify = Verify::type(1)->with('user')->whereToken($token)->firstOrFail();
        $user = $verify->user;
        if (! $verify) {
            return redirect()->route('login');
        }

        if (empty($user) || $verify->status > 0) {
            session()->flash('errorMsg', trans('auth.error.url_timeout'));

            return view('auth.active');
        }

        if ($user->status === 1) {
            session()->flash('errorMsg', trans('auth.active.error.activated'));

            return view('auth.active');
        }

        if (time() - strtotime($verify->created_at) >= 1800) {
            session()->flash('errorMsg', trans('auth.error.url_timeout'));

            // 置为已失效
            $verify->update(['status' => 2]);

            return view('auth.active');
        }

        // 更新账号状态
        if (! $user->update(['status' => 1])) {
            session()->flash('errorMsg', trans('common.active_item', ['attribute' => trans('common.failed')]));

            return redirect()->back();
        }

        // 置为已使用
        $verify->update(['status' => 1]);

        // 账号激活后给邀请人送流量
        $inviter = $user->inviter;
        if ($inviter) {
            $inviter->incrementData(sysConfig('referral_traffic') * MiB);
        }

        session()->flash('successMsg', trans('common.active_item', ['attribute' => trans('common.success')]));

        return view('auth.active');
    }

    public function sendCode(Request $request): JsonResponse
    { // 发送注册验证码
        $validator = Validator::make($request->all(), ['username' => 'required|'.(sysConfig('username_type') ?? 'email').'|unique:user,username']);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->getMessageBag()->first()]);
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
            return response()->json(['status' => 'fail', 'message' => trans('auth.active.error.disable')]);
        }

        // 防刷机制
        if (cache()->has('send_verify_code_'.md5($ip))) {
            return response()->json(['status' => 'fail', 'message' => trans('auth.register.error.throttle')]);
        }

        // 发送邮件
        $code = Str::random(6);
        if (VerifyCode::create(['address' => $email, 'code' => $code])) { // 生成注册验证码
            Notification::route('mail', $email)->notifyNow(new Verification($code));
        }

        cache()->put('send_verify_code_'.md5($ip), $ip, Minute);

        return response()->json(['status' => 'success', 'message' => trans('auth.captcha.sent')]);
    }

    public function free(): View
    { // 公开的邀请码列表
        return view('auth.free', ['inviteList' => Invite::whereInviterId(null)->whereStatus(0)->paginate()]);
    }

    public function switchLang(string $locale): RedirectResponse
    { // 切换语言
        if (array_key_exists($locale, config('common.language'))) {
            session()->put('locale', $locale);
        }

        return redirect()->back();
    }
}
