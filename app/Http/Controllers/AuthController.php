<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\IPIP;
use App\Components\QQWry;
use App\Components\CaptchaVerify;
use App\Http\Models\Invite;
use App\Http\Models\User;
use App\Http\Models\UserLoginLog;
use App\Http\Models\UserLabel;
use App\Http\Models\UserSubscribe;
use App\Http\Models\Verify;
use App\Http\Models\VerifyCode;
use App\Mail\activeUser;
use App\Mail\resetPassword;
use App\Mail\sendVerifyCode;
use Illuminate\Http\Request;
use Validator;
use Response;
use Redirect;
use Captcha;
use Session;
use Cache;
use Auth;
use Mail;
use Hash;
use Log;

/**
 * 认证控制器
 *
 * Class AuthController
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    // 登录
    public function login(Request $request)
    {
        if ($request->isMethod('POST')) {
            $this->validate($request, [
                'username' => 'required',
                'password' => 'required'
            ], [
                'username.required' => '请输入用户名',
                'password.required' => '请输入密码'
            ]);

            // 是否校验验证码
            switch (self::$systemConfig['is_captcha']) {
                case 1: // 默认图形验证码
                    if (!Captcha::check($request->captcha)) {
                        return Redirect::back()->withInput()->withErrors('验证码错误，请重新输入');
                    }
                    break;
                case 2: // Geetest
                    $result = $this->validate($request, [
                        'geetest_challenge' => 'required|geetest'
                    ], [
                        'geetest' => trans('login.fail_captcha')
                    ]);

                    if (!$result) {
                        return Redirect::back()->withInput()->withErrors(trans('login.fail_captcha'));
                    }
                    break;
                case 3: // Google reCAPTCHA
                    $result = $this->validate($request, [
                        'g-recaptcha-response' => 'required|NoCaptcha'
                    ]);

                    if (!$result) {
                        return Redirect::back()->withInput()->withErrors(trans('login.fail_captcha'));
                    }
                    break;
                default: // 不启用验证码
                    break;
            }

            // 验证账号并创建会话
            if (!Auth::attempt(['username' => $request->username, 'password' => $request->password], $request->remember)) {
                return Redirect::back()->withInput()->withErrors('用户名或密码错误');
            }

            // 校验普通用户账号状态
            if (!Auth::user()->is_admin) {
                if (Auth::user()->status < 0) {
                    Auth::logout(); // 强制销毁会话，因为Auth::attempt的时候会产生会话

                    return Redirect::back()->withInput()->withErrors('账号已禁用');
                }

                if (Auth::user()->status == 0 && self::$systemConfig['is_active_register']) {
                    Auth::logout(); // 强制销毁会话，因为Auth::attempt的时候会产生会话

                    return Redirect::back()->withInput()->withErrors('账号未激活，请点击<a href="/activeUser?username=' . $request->username . '" target="_blank"><span style="color:#000">【激活账号】</span></a>');
                }
            }

            // 写入登录日志
            $this->addUserLoginLog(Auth::user()->id, getClientIp());

            // 更新登录信息
            User::uid()->update(['last_login' => time()]);

            // 根据权限跳转
            if (Auth::user()->is_admin) {
                return Redirect::to('admin');
            }

            return Redirect::to('/');
        } else {
            if (Auth::check()) {
                if (Auth::user()->is_admin) {
                    return Redirect::to('admin');
                }

                return Redirect::to('/');
            }

            return Response::view('auth.login');
        }
    }

    // 退出
    public function logout(Request $request)
    {
        Auth::logout();

        return Redirect::to('login');
    }

    // 注册
    public function register(Request $request)
    {
        $cacheKey = 'register_times_' . md5(getClientIp()); // 注册限制缓存key

        if ($request->isMethod('POST')) {
            $this->validate($request, [
                'username'   => 'required|email|unique:user',
                'password'   => 'required|min:6',
                'repassword' => 'required|same:password',
            ], [
                'username.required'   => '请输入用户名',
                'username.email'      => '用户名必须是合法邮箱',
                'username.unique'     => '用户已存在，如果忘记密码请找回密码',
                'password.required'   => '请输入密码',
                'password.min'        => '密码最少要6位数',
                'repassword.required' => '请再次输入密码',
                'repassword.same'     => '两次输入密码不一致'
            ]);

            // 防止重复提交
            if ($request->register_token != Session::get('register_token')) {
                return Redirect::back()->withInput()->withErrors('请勿重复请求，刷新一下页面再试试');
            } else {
                Session::forget('register_token');
            }

            // 是否开启注册
            if (!self::$systemConfig['is_register']) {
                return Redirect::back()->withErrors('系统维护，暂停注册');
            }

            // 校验域名邮箱是否在敏感词中
            $sensitiveWords = $this->sensitiveWords();
            $usernameSuffix = explode('@', $request->username); // 提取邮箱后缀
            if (in_array(strtolower($usernameSuffix[1]), $sensitiveWords)) {
                return Redirect::back()->withInput()->withErrors('邮箱含有敏感词，请重新输入');
            }

            // 如果需要邀请注册
            if (self::$systemConfig['is_invite_register']) {
                // 必须使用邀请码
                if (self::$systemConfig['is_invite_register'] == 2 && !$request->code) {
                    return Redirect::back()->withInput()->withErrors('请输入邀请码');
                }

                // 校验邀请码合法性
                if ($request->code) {
                    $codeEnable = Invite::query()->where('code', $request->code)->where('status', 0)->first();
                    if (!$codeEnable) {
                        return Redirect::back()->withInput($request->except(['code']))->withErrors('邀请码不可用，请重试');
                    }
                }
            }

            // 如果开启注册发送验证码
            if (self::$systemConfig['is_verify_register']) {
                if (!$request->verify_code) {
                    return Redirect::back()->withInput($request->except(['verify_code']))->withErrors('请输入验证码');
                } else {
                    $verifyCode = VerifyCode::query()->where('username', $request->username)->where('code', $request->verify_code)->where('status', 0)->first();
                    if (!$verifyCode) {
                        return Redirect::back()->withInput($request->except(['verify_code']))->withErrors('验证码不合法，可能已过期，请重试');
                    }

                    $verifyCode->status = 1;
                    $verifyCode->save();
                }
            } elseif (self::$systemConfig['is_captcha']) { // 是否校验验证码
                switch (self::$systemConfig['is_captcha']) {
                    case 1: // 默认图形验证码
                        if (!Captcha::check($request->captcha)) {
                            return Redirect::back()->withInput()->withErrors('验证码错误，请重新输入');
                        }
                        break;
                    case 2: // Geetest
                        $result = $this->validate($request, [
                            'geetest_challenge' => 'required|geetest'
                        ], [
                            'geetest' => trans('login.fail_captcha')
                        ]);

                        if (!$result) {
                            return Redirect::back()->withInput()->withErrors(trans('login.fail_captcha'));
                        }
                        break;
                    case 3: // Google reCAPTCHA
                        $result = $this->validate($request, [
                            'g-recaptcha-response' => 'required|NoCaptcha'
                        ]);

                        if (!$result) {
                            return Redirect::back()->withInput()->withErrors(trans('login.fail_captcha'));
                        }
                        break;
                    default: // 不启用验证码
                        break;
                }
            }

            // 24小时内同IP注册限制
            if (self::$systemConfig['register_ip_limit']) {
                if (Cache::has($cacheKey)) {
                    $registerTimes = Cache::get($cacheKey);
                    if ($registerTimes >= self::$systemConfig['register_ip_limit']) {
                        return Redirect::back()->withInput($request->except(['code']))->withErrors('系统已开启防刷机制，请勿频繁注册');
                    }
                }
            }

            // 获取可用端口
            $port = self::$systemConfig['is_rand_port'] ? Helpers::getRandPort() : Helpers::getOnlyPort();
            if ($port > self::$systemConfig['max_port']) {
                return Redirect::back()->withInput()->withErrors('系统不再接受新用户，请联系管理员');
            }

            // 获取aff
            $affArr = $this->getAff($request->code, intval($request->aff));
            $referral_uid = $affArr['referral_uid'];

            $transfer_enable = $referral_uid ? (self::$systemConfig['default_traffic'] + self::$systemConfig['referral_traffic']) * 1048576 : self::$systemConfig['default_traffic'] * 1048576;

            // 创建新用户
            $user = new User();
            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->port = $port;
            $user->passwd = makeRandStr();
            $user->vmess_id = createGuid();
            $user->transfer_enable = $transfer_enable;
            $user->method = Helpers::getDefaultMethod();
            $user->protocol = Helpers::getDefaultProtocol();
            $user->obfs = Helpers::getDefaultObfs();
            $user->enable_time = date('Y-m-d H:i:s');
            $user->expire_time = date('Y-m-d H:i:s', strtotime("+" . self::$systemConfig['default_days'] . " days"));
            $user->reg_ip = getClientIp();
            $user->referral_uid = $referral_uid;
            $user->save();

            // 注册失败，抛出异常
            if (!$user->id) {
                return Redirect::back()->withInput()->withErrors('注册失败，请联系管理员');
            }

            // 生成订阅码
            $subscribe = new UserSubscribe();
            $subscribe->user_id = $user->id;
            $subscribe->code = Helpers::makeSubscribeCode();
            $subscribe->times = 0;
            $subscribe->save();

            // 注册次数+1
            if (Cache::has($cacheKey)) {
                Cache::increment($cacheKey);
            } else {
                Cache::put($cacheKey, 1, 1440); // 24小时
            }

            // 初始化默认标签
            if (strlen(self::$systemConfig['initial_labels_for_user'])) {
                $labels = explode(',', self::$systemConfig['initial_labels_for_user']);
                foreach ($labels as $label) {
                    $userLabel = new UserLabel();
                    $userLabel->user_id = $user->id;
                    $userLabel->label_id = $label;
                    $userLabel->save();
                }
            }

            // 更新邀请码
            if (self::$systemConfig['is_invite_register'] && $affArr['code_id']) {
                Invite::query()->where('id', $affArr['code_id'])->update(['fuid' => $user->id, 'status' => 1]);
            }

            // 清除邀请人Cookie
            \Cookie::unqueue('register_aff');

            if (self::$systemConfig['is_verify_register']) {
                if ($referral_uid) {
                    $transfer_enable = self::$systemConfig['referral_traffic'] * 1048576;

                    User::query()->where('id', $referral_uid)->increment('transfer_enable', $transfer_enable);
                    User::query()->where('id', $referral_uid)->update(['status' => 1, 'enable' => 1]);
                }

                User::query()->where('id', $user->id)->update(['status' => 1, 'enable' => 1]);

                Session::flash('regSuccessMsg', '注册成功');
            } else {
                // 发送激活邮件
                if (self::$systemConfig['is_active_register']) {
                    // 生成激活账号的地址
                    $token = md5(self::$systemConfig['website_name'] . $request->username . microtime());
                    $activeUserUrl = self::$systemConfig['website_url'] . '/active/' . $token;
                    $this->addVerify($user->id, $token);

                    $logId = Helpers::addEmailLog($request->username, '注册激活', '请求地址：' . $activeUserUrl);
                    Mail::to($request->username)->send(new activeUser($logId, $activeUserUrl));

                    Session::flash('regSuccessMsg', '注册成功：激活邮件已发送，如未收到，请查看垃圾邮箱');
                } else {
                    // 如果不需要激活，则直接给推荐人加流量
                    if ($referral_uid) {
                        $transfer_enable = self::$systemConfig['referral_traffic'] * 1048576;

                        User::query()->where('id', $referral_uid)->increment('transfer_enable', $transfer_enable);
                        User::query()->where('id', $referral_uid)->update(['status' => 1, 'enable' => 1]);
                    }

                    User::query()->where('id', $user->id)->update(['status' => 1, 'enable' => 1]);

                    Session::flash('regSuccessMsg', '注册成功');
                }
            }

            return Redirect::to('login')->withInput();
        } else {
            Session::put('register_token', makeRandStr(16));

            return Response::view('auth.register');
        }
    }

    // 重设密码页
    public function resetPassword(Request $request)
    {
        if ($request->isMethod('POST')) {
            // 校验请求
            $this->validate($request, [
                'username' => 'required|email'
            ], [
                'username.required' => '请输入用户名',
                'username.email'    => '用户名必须是合法邮箱'
            ]);

            // 是否开启重设密码
            if (!self::$systemConfig['is_reset_password']) {
                return Redirect::back()->withErrors('系统未开启重置密码功能，请联系管理员');
            }

            // 查找账号
            $user = User::query()->where('username', $request->username)->first();
            if (!$user) {
                return Redirect::back()->withErrors('账号不存在，请重试');
            }

            // 24小时内重设密码次数限制
            $resetTimes = 0;
            if (Cache::has('resetPassword_' . md5($request->username))) {
                $resetTimes = Cache::get('resetPassword_' . md5($request->username));
                if ($resetTimes >= self::$systemConfig['reset_password_times']) {
                    return Redirect::back()->withErrors('同一个账号24小时内只能重设密码' . self::$systemConfig['reset_password_times'] . '次，请勿频繁操作');
                }
            }

            // 生成取回密码的地址
            $token = md5(self::$systemConfig['website_name'] . $request->username . microtime());
            $verify = new Verify();
            $verify->type = 1;
            $verify->user_id = $user->id;
            $verify->token = $token;
            $verify->status = 0;
            $verify->save();

            // 发送邮件
            $resetPasswordUrl = self::$systemConfig['website_url'] . '/reset/' . $token;

            $logId = Helpers::addEmailLog($request->username, '重置密码', '请求地址：' . $resetPasswordUrl);
            Mail::to($request->username)->send(new resetPassword($logId, $resetPasswordUrl));

            Cache::put('resetPassword_' . md5($request->username), $resetTimes + 1, 1440);

            return Redirect::back()->with('successMsg', '重置成功，请查看邮箱');
        } else {
            return Response::view('auth.resetPassword');
        }
    }

    // 重设密码
    public function reset(Request $request, $token)
    {
        if (!$token) {
            return Redirect::to('login');
        }

        if ($request->isMethod('POST')) {
            $this->validate($request, [
                'password'   => 'required|min:6',
                'repassword' => 'required|same:password'
            ], [
                'password.required'   => '密码不能为空',
                'password.min'        => '密码最少要6位数',
                'repassword.required' => '密码不能为空',
                'repassword.min'      => '密码最少要6位数',
                'repassword.same'     => '两次输入密码不一致',
            ]);

            // 校验账号
            $verify = Verify::type(1)->with('user')->where('token', $token)->first();
            if (!$verify) {
                return Redirect::to('login');
            } elseif ($verify->status == 1) {
                return Redirect::back()->withErrors('该链接已失效');
            } elseif ($verify->user->status < 0) {
                return Redirect::back()->withErrors('账号已被禁用');
            } elseif (Hash::check($request->password, $verify->user->password)) {
                return Redirect::back()->withErrors('新旧密码一样，请重新输入');
            }

            // 更新密码
            $ret = User::query()->where('id', $verify->user_id)->update(['password' => Hash::make($request->password)]);
            if (!$ret) {
                return Redirect::back()->withErrors('重设密码失败');
            }

            // 置为已使用
            $verify->status = 1;
            $verify->save();

            return Redirect::back()->with('successMsg', '新密码设置成功，请自行登录');
        } else {
            $verify = Verify::type(1)->where('token', $token)->first();
            if (!$verify) {
                return Redirect::to('login');
            } elseif (time() - strtotime($verify->created_at) >= 1800) {
                // 置为已失效
                $verify->status = 2;
                $verify->save();
            }

            // 重新获取一遍verify
            $view['verify'] = Verify::type(1)->where('token', $token)->first();

            return Response::view('auth.reset', $view);
        }
    }

    // 激活账号页
    public function activeUser(Request $request)
    {
        if ($request->isMethod('POST')) {
            $this->validate($request, [
                'username' => 'required|email|exists:user,username'
            ], [
                'username.required' => '请输入用户名',
                'username.email'    => '用户名必须是合法邮箱',
                'username.exists'   => '账号不存在，请重试'
            ]);

            // 是否开启账号激活
            if (!self::$systemConfig['is_active_register']) {
                return Redirect::back()->withInput()->withErrors('系统未开启账号激活功能，请联系管理员');
            }

            // 查找账号
            $user = User::query()->where('username', $request->username)->first();
            if ($user->status < 0) {
                return Redirect::back()->withErrors('账号已封禁，请联系管理员');
            } elseif ($user->status > 0) {
                return Redirect::back()->withErrors('账号状态正常，无需激活');
            }

            // 24小时内激活次数限制
            $activeTimes = 0;
            if (Cache::has('activeUser_' . md5($request->username))) {
                $activeTimes = Cache::get('activeUser_' . md5($request->username));
                if ($activeTimes >= self::$systemConfig['active_times']) {
                    return Redirect::back()->withErrors('同一个账号24小时内只能请求激活' . self::$systemConfig['active_times'] . '次，请勿频繁操作');
                }
            }

            // 生成激活账号的地址
            $token = md5(self::$systemConfig['website_name'] . $request->username . microtime());
            $verify = new Verify();
            $verify->type = 1;
            $verify->user_id = $user->id;
            $verify->token = $token;
            $verify->status = 0;
            $verify->save();

            // 发送邮件
            $activeUserUrl = self::$systemConfig['website_url'] . '/active/' . $token;

            $logId = Helpers::addEmailLog($request->username, '激活账号', '请求地址：' . $activeUserUrl);
            Mail::to($request->username)->send(new activeUser($logId, $activeUserUrl));

            Cache::put('activeUser_' . md5($request->username), $activeTimes + 1, 1440);

            return Redirect::back()->with('successMsg', '激活邮件已发送，如未收到，请查看垃圾箱');
        } else {
            return Response::view('auth.activeUser');
        }
    }

    // 激活账号
    public function active(Request $request, $token)
    {
        if (!$token) {
            return Redirect::to('login');
        }

        $verify = Verify::type(1)->with('user')->where('token', $token)->first();
        if (!$verify) {
            return Redirect::to('login');
        } elseif (empty($verify->user)) {
            Session::flash('errorMsg', '该链接已失效');

            return Response::view('auth.active');
        } elseif ($verify->status > 0) {
            Session::flash('errorMsg', '该链接已失效');

            return Response::view('auth.active');
        } elseif ($verify->user->status != 0) {
            Session::flash('errorMsg', '该账号无需激活.');

            return Response::view('auth.active');
        } elseif (time() - strtotime($verify->created_at) >= 1800) {
            Session::flash('errorMsg', '该链接已过期');

            // 置为已失效
            $verify->status = 2;
            $verify->save();

            return Response::view('auth.active');
        }

        // 更新账号状态
        $ret = User::query()->where('id', $verify->user_id)->update(['status' => 1]);
        if (!$ret) {
            Session::flash('errorMsg', '账号激活失败');

            return Redirect::back();
        }

        // 置为已使用
        $verify->status = 1;
        $verify->save();

        // 账号激活后给邀请人送流量
        if ($verify->user->referral_uid) {
            $transfer_enable = self::$systemConfig['referral_traffic'] * 1048576;

            User::query()->where('id', $verify->user->referral_uid)->increment('transfer_enable', $transfer_enable);
            User::query()->where('id', $verify->user->referral_uid)->update(['enable' => 1]);
        }

        Session::flash('successMsg', '账号激活成功');

        return Response::view('auth.active');
    }

    // 发送注册验证码
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|email|unique:user'
        ], [
            'username.required' => '请填入邮箱',
            'username.email'    => '邮箱地址不合法，请重新输入',
            'username.unique'   => '用户已存在，如果忘记密码请找回密码'
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => $validator->getMessageBag()->first()]);
        }

        // 校验域名邮箱是否在敏感词中
        $sensitiveWords = $this->sensitiveWords();
        $usernameSuffix = explode('@', $request->username); // 提取邮箱后缀
        if (in_array(strtolower($usernameSuffix[1]), $sensitiveWords)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '邮箱含有敏感词，请重新输入']);
        }

        // 是否开启注册发送验证码
        if (!self::$systemConfig['is_verify_register']) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '系统未启用通过验证码注册']);
        }

        // 防刷机制
        if (Cache::has('send_verify_code_' . md5(getClientIP()))) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '系统已开启防刷机制，请勿频繁请求']);
        }

        // 发送邮件
        $code = makeRandStr(6, true);
        $logId = Helpers::addEmailLog($request->username, '发送注册验证码', '验证码：' . $code);
        Mail::to($request->username)->send(new sendVerifyCode($logId, $code));

        $this->addVerifyCode($request->username, $code);

        Cache::put('send_verify_code_' . md5(getClientIP()), getClientIP(), 1);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '验证码已发送']);
    }

    // 公开的邀请码列表
    public function free(Request $request)
    {
        $view['inviteList'] = Invite::query()->where('uid', 0)->where('status', 0)->paginate();

        return Response::view('auth.free', $view);
    }

    // 切换语言
    public function switchLang(Request $request, $locale)
    {
        Session::put("locale", $locale);

        return Redirect::back();
    }

    /**
     * 添加用户登录日志
     *
     * @param string $userId 用户ID
     * @param string $ip     IP地址
     */
    private function addUserLoginLog($userId, $ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            Log::info('识别到IPv6，尝试解析：' . $ip);
            $ipInfo = getIPv6($ip);
        } else {
            $ipInfo = QQWry::ip($ip); // 通过纯真IP库解析IPv4信息
            if (isset($ipInfo['error'])) {
                Log::info('无法识别IPv4，尝试使用IPIP的IP库解析：' . $ip);
                $ipip = IPIP::ip($ip);
                $ipInfo = [
                    'country'  => $ipip['country_name'],
                    'province' => $ipip['region_name'],
                    'city'     => $ipip['city_name']
                ];
            } else {
                // 判断纯真IP库获取的国家信息是否与IPIP的IP库获取的信息一致，不一致则用IPIP的（因为纯真IP库的非大陆IP准确率较低）
                $ipip = IPIP::ip($ip);
                if ($ipInfo['country'] != $ipip['country_name']) {
                    $ipInfo['country'] = $ipip['country_name'];
                    $ipInfo['province'] = $ipip['region_name'];
                    $ipInfo['city'] = $ipip['city_name'];
                }
            }
        }

        if (empty($ipInfo) || empty($ipInfo['country'])) {
            Log::warning("获取IP信息异常：" . $ip);
        }

        $log = new UserLoginLog();
        $log->user_id = $userId;
        $log->ip = $ip;
        $log->country = $ipInfo['country'] ?? '';
        $log->province = $ipInfo['province'] ?? '';
        $log->city = $ipInfo['city'] ?? '';
        $log->county = $ipInfo['county'] ?? '';
        $log->isp = $ipInfo['isp'] ?? ($ipInfo['organization'] ?? '');
        $log->area = $ipInfo['area'] ?? '';
        $log->save();
    }

    /**
     * 获取AFF
     *
     * @param string $code 邀请码
     * @param int    $aff  URL中的aff参数
     *
     * @return array
     */
    private function getAff($code = '', $aff = '')
    {
        // 邀请人ID
        $referral_uid = 0;

        // 邀请码ID
        $code_id = 0;

        // 有邀请码先用邀请码，用谁的邀请码就给谁返利
        if ($code) {
            $inviteCode = Invite::query()->where('code', $code)->where('status', 0)->first();
            if ($inviteCode) {
                $referral_uid = $inviteCode->uid;
                $code_id = $inviteCode->id;
            }
        }

        // 没有用邀请码或者邀请码是管理员生成的，则检查cookie或者url链接
        if (!$referral_uid) {
            // 检查一下cookie里有没有aff
            $cookieAff = \Request::hasCookie('register_aff') ? \Request::cookie('register_aff') : 0;
            if ($cookieAff) {
                $affUser = User::query()->where('id', $cookieAff)->exists();
                $referral_uid = $affUser ? $cookieAff : 0;
            } elseif ($aff) { // 如果cookie里没有aff，就再检查一下请求的url里有没有aff，因为有些人的浏览器会禁用了cookie，比如chrome开了隐私模式
                $affUser = User::query()->where('id', $aff)->exists();
                $referral_uid = $affUser ? $aff : 0;
            }
        }

        return [
            'referral_uid' => $referral_uid,
            'code_id'      => $code_id
        ];
    }

    // 写入生成激活账号验证记录
    private function addVerify($userId, $token)
    {
        $verify = new Verify();
        $verify->type = 1;
        $verify->user_id = $userId;
        $verify->token = $token;
        $verify->status = 0;
        $verify->save();
    }

    // 生成注册验证码
    private function addVerifyCode($username, $code)
    {
        $verify = new VerifyCode();
        $verify->username = $username;
        $verify->code = $code;
        $verify->status = 0;
        $verify->save();
    }
}