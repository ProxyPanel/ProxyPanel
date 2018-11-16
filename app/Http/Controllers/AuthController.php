<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\QQWry;
use App\Http\Models\Invite;
use App\Http\Models\User;
use App\Http\Models\UserLoginLog;
use App\Http\Models\UserLabel;
use App\Http\Models\Verify;
use App\Http\Models\VerifyCode;
use App\Mail\activeUser;
use App\Mail\resetPassword;
use App\Mail\sendVerifyCode;
use Illuminate\Http\Request;
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
 * 验证控制器
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
        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));
            $password = trim($request->get('password'));
            $captcha = trim($request->get('captcha'));
            $remember = trim($request->get('remember'));

            if (empty($username) || empty($password)) {
                Session::flash('errorMsg', '请输入用户名和密码');

                return Redirect::back();
            }

            // 是否校验验证码
            if (self::$systemConfig['is_captcha']) {
                if (!Captcha::check($captcha)) {
                    Session::flash('errorMsg', '验证码错误，请重新输入');

                    return Redirect::back()->withInput();
                }
            }

            if (!Auth::attempt(['username' => $username, 'password' => $password], $remember)) {
                Session::flash('errorMsg', '用户名或密码错误');

                return Redirect::back()->withInput();
            } elseif (!Auth::user()->is_admin && Auth::user()->status < 0) {
                Session::flash('errorMsg', '账号已禁用');

                return Redirect::back();
            } elseif (Auth::user()->status == 0 && self::$systemConfig['is_active_register'] && Auth::user()->is_admin == 0) {
                Session::flash('errorMsg', '账号未激活，请点击<a href="/activeUser?username=' . Auth::user()->username . '" target="_blank"><span style="color:#000">【激活账号】</span></a>');

                return Redirect::back()->withInput();
            }

            // 登录送积分
            if (self::$systemConfig['login_add_score']) {
                if (!Cache::has('loginAddScore_' . md5($username))) {
                    $score = mt_rand(self::$systemConfig['min_rand_score'], self::$systemConfig['max_rand_score']);
                    $ret = User::query()->where('id', Auth::user()->id)->increment('score', $score);
                    if ($ret) {
                        $this->addUserScoreLog(Auth::user()->id, Auth::user()->score, Auth::user()->score + $score, $score, '登录送积分');

                        // 登录多久后再登录可以获取积分
                        $ttl = self::$systemConfig['login_add_score_range'] ? self::$systemConfig['login_add_score_range'] : 1440;
                        Cache::put('loginAddScore_' . md5($username), '1', $ttl);

                        Session::flash('successMsg', '欢迎回来，系统自动赠送您 ' . $score . ' 积分，您可以用它兑换流量');
                    }
                }
            }

            // 写入登录日志
            $this->addUserLoginLog(Auth::user()->id, getClientIp());

            // 更新登录信息
            User::query()->where('id', Auth::user()->id)->update(['last_login' => time()]);

            // 根据权限跳转
            if (Auth::user()->is_admin) {
                return Redirect::to('admin');
            }

            return Redirect::to('/');
        } else {
            if (Auth::viaRemember()) {
                if (Auth::check()) {
                    if (Auth::user()->is_admin) {
                        return Redirect::to('admin');
                    }

                    return Redirect::to('/');
                }
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

        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));
            $password = trim($request->get('password'));
            $repassword = trim($request->get('repassword'));
            $captcha = trim($request->get('captcha'));
            $code = trim($request->get('code'));
            $verify_code = trim($request->get('verify_code'));
            $register_token = $request->get('register_token');
            $aff = intval($request->get('aff', 0));

            // 防止重复提交
            $session_register_token = Session::get('register_token');
            if (empty($register_token) || $register_token != $session_register_token) {
                Session::flash('errorMsg', '请勿重复请求，刷新一下页面再试试');

                return Redirect::back()->withInput();
            } else {
                Session::forget('register_token');
            }

            // 是否开启注册
            if (!self::$systemConfig['is_register']) {
                Session::flash('errorMsg', '系统维护，暂停注册');

                return Redirect::back();
            }

            if (empty($username)) {
                Session::flash('errorMsg', '请输入用户名');

                return Redirect::back()->withInput();
            } elseif (empty($password)) {
                Session::flash('errorMsg', '请输入密码');

                return Redirect::back()->withInput();
            } elseif (empty($repassword)) {
                Session::flash('errorMsg', '请重新输入密码');

                return Redirect::back()->withInput();
            } elseif (md5($password) != md5($repassword)) {
                Session::flash('errorMsg', '两次输入密码不一致，请重新输入');

                return Redirect::back()->withInput($request->except(['password', 'repassword']));
            } elseif (false === filter_var($username, FILTER_VALIDATE_EMAIL)) {
                Session::flash('errorMsg', '用户名必须是合法邮箱，请重新输入');

                return Redirect::back()->withInput();
            }

            // 校验域名邮箱是否在敏感词中
            $sensitiveWords = $this->sensitiveWords();
            $usernameSuffix = explode('@', $username); // 提取邮箱后缀
            if (in_array(strtolower($usernameSuffix[1]), $sensitiveWords)) {
                Session::flash('errorMsg', '邮箱含有敏感词，请重新输入');

                return Redirect::back()->withInput();
            }

            // 如果需要邀请注册
            if (self::$systemConfig['is_invite_register']) {
                // 必须使用邀请码
                if (self::$systemConfig['is_invite_register'] == 2 && empty($code)) {
                    Session::flash('errorMsg', '请输入邀请码');

                    return Redirect::back()->withInput();
                }

                // 校验邀请码合法性
                if (!empty($code)) {
                    $codeEnable = Invite::query()->where('code', $code)->where('status', 0)->first();
                    if (empty($codeEnable)) {
                        Session::flash('errorMsg', '邀请码不可用，请更换邀请码后重试');

                        return Redirect::back()->withInput($request->except(['code']));
                    }
                }
            }

            // 如果开启注册发送验证码
            if (self::$systemConfig['is_verify_register']) {
                if (!$verify_code) {
                    Session::flash('errorMsg', '请输入验证码');

                    return Redirect::back()->withInput($request->except(['verify_code']));
                } else {
                    $verifyCode = VerifyCode::query()->where('username', $username)->where('code', $verify_code)->where('status', 0)->first();
                    if (!$verifyCode) {
                        Session::flash('errorMsg', '验证码不合法，可能已过期，请重试');

                        return Redirect::back()->withInput($request->except(['verify_code']));
                    }

                    $verifyCode->status = 1;
                    $verifyCode->save();
                }
            } elseif (self::$systemConfig['is_captcha']) { // 是否校验验证码
                if (!Captcha::check($captcha)) {
                    Session::flash('errorMsg', '验证码错误，请重新输入');

                    return Redirect::back()->withInput($request->except(['captcha']));
                }
            }

            // 24小时内同IP注册限制
            if (self::$systemConfig['register_ip_limit']) {
                if (Cache::has($cacheKey)) {
                    $registerTimes = Cache::get($cacheKey);
                    if ($registerTimes >= self::$systemConfig['register_ip_limit']) {
                        Session::flash('errorMsg', '系统已开启防刷机制，请勿频繁注册');

                        return Redirect::back()->withInput($request->except(['code']));
                    }
                }
            }

            // 校验用户名是否已存在
            $exists = User::query()->where('username', $username)->exists();
            if ($exists) {
                Session::flash('errorMsg', '用户名已存在，请更换用户名');

                return Redirect::back()->withInput();
            }

            // 获取可用端口
            $port = self::$systemConfig['is_rand_port'] ? Helpers::getRandPort() : Helpers::getOnlyPort();
            if ($port > self::$systemConfig['max_port']) {
                Session::flash('errorMsg', '用户已满，请联系管理员');

                return Redirect::back()->withInput();
            }

            // 获取aff
            $affArr = $this->getAff($code, $aff);
            $referral_uid = $affArr['referral_uid'];

            $transfer_enable = $referral_uid ? (self::$systemConfig['default_traffic'] + self::$systemConfig['referral_traffic']) * 1048576 : self::$systemConfig['default_traffic'] * 1048576;

            // 创建新用户
            $user = new User();
            $user->username = $username;
            $user->password = Hash::make($password);
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
                Session::flash('errorMsg', '注册失败，请联系管理员');

                return Redirect::back()->withInput();
            }

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
                    $token = md5(self::$systemConfig['website_name'] . $username . microtime());
                    $activeUserUrl = self::$systemConfig['website_url'] . '/active/' . $token;
                    $this->addVerify($user->id, $token);

                    try {
                        Mail::to($username)->send(new activeUser($activeUserUrl));
                        Helpers::addEmailLog($username, '注册激活', '请求地址：' . $activeUserUrl);
                    } catch (\Exception $e) {
                        Helpers::addEmailLog($username, '注册激活', '请求地址：' . $activeUserUrl, 0, $e->getMessage());
                    }

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

            return Redirect::to('login');
        } else {
            Session::put('register_token', makeRandStr(16));

            return Response::view('auth.register');
        }
    }

    // 重设密码页
    public function resetPassword(Request $request)
    {
        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));

            // 校验账号合法性
            if (false === filter_var($username, FILTER_VALIDATE_EMAIL)) {
                Session::flash('errorMsg', '用户名必须是合法邮箱，请重新输入');

                return Redirect::back();
            }

            // 是否开启重设密码
            if (!self::$systemConfig['is_reset_password']) {
                Session::flash('errorMsg', '系统未开启重置密码功能，请联系管理员');

                return Redirect::back();
            }

            // 查找账号
            $user = User::query()->where('username', $username)->first();
            if (!$user) {
                Session::flash('errorMsg', '账号不存在，请重试');

                return Redirect::back();
            }

            // 24小时内重设密码次数限制
            $resetTimes = 0;
            if (Cache::has('resetPassword_' . md5($username))) {
                $resetTimes = Cache::get('resetPassword_' . md5($username));
                if ($resetTimes >= self::$systemConfig['reset_password_times']) {
                    Session::flash('errorMsg', '同一个账号24小时内只能重设密码' . self::$systemConfig['reset_password_times'] . '次，请勿频繁操作');

                    return Redirect::back();
                }
            }

            // 生成取回密码的地址
            $token = md5(self::$systemConfig['website_name'] . $username . microtime());
            $verify = new Verify();
            $verify->type = 1;
            $verify->user_id = $user->id;
            $verify->token = $token;
            $verify->status = 0;
            $verify->save();

            // 发送邮件
            $resetPasswordUrl = self::$systemConfig['website_url'] . '/reset/' . $token;
            $title = '重置密码';
            $content = '请求地址：' . $resetPasswordUrl;

            try {
                Mail::to($username)->send(new resetPassword($resetPasswordUrl));
                Helpers::addEmailLog($username, $title, $content);
            } catch (\Exception $e) {
                Helpers::addEmailLog($username, $title, $content, 0, $e->getMessage());
            }

            Cache::put('resetPassword_' . md5($username), $resetTimes + 1, 1440);
            Session::flash('successMsg', '重置成功，请查看邮箱');

            return Redirect::back();
        } else {
            return Response::view('auth.resetPassword');
        }
    }

    // 重设密码
    public function reset(Request $request, $token)
    {
        if ($request->method() == 'POST') {
            $password = trim($request->get('password'));
            $repassword = trim($request->get('repassword'));

            if (empty($token)) {
                return Redirect::to('login');
            } elseif (empty($password) || empty($repassword)) {
                Session::flash('errorMsg', '密码不能为空');

                return Redirect::back();
            } elseif (md5($password) != md5($repassword)) {
                Session::flash('errorMsg', '两次输入密码不一致，请重新输入');

                return Redirect::back();
            }

            // 校验账号
            $verify = Verify::query()->where('type', 1)->where('token', $token)->with('User')->first();
            if (empty($verify)) {
                return Redirect::to('login');
            } elseif ($verify->status == 1) {
                Session::flash('errorMsg', '该链接已失效');

                return Redirect::back();
            } elseif ($verify->user->status < 0) {
                Session::flash('errorMsg', '账号已被禁用');

                return Redirect::back();
            } elseif (Hash::check($password, $verify->user->password)) {
                Session::flash('errorMsg', '新旧密码一样，请重新输入');

                return Redirect::back();
            }

            // 更新密码
            $ret = User::query()->where('id', $verify->user_id)->update(['password' => Hash::make($password)]);
            if (!$ret) {
                Session::flash('errorMsg', '重设密码失败');

                return Redirect::back();
            }

            // 置为已使用
            $verify->status = 1;
            $verify->save();

            Session::flash('successMsg', '新密码设置成功，请自行登录');

            return Redirect::back();
        } else {
            if (empty($token)) {
                return Redirect::to('login');
            }

            $verify = Verify::query()->where('type', 1)->where('token', $token)->with('user')->first();
            if (empty($verify)) {
                return Redirect::to('login');
            } elseif (time() - strtotime($verify->created_at) >= 1800) {
                Session::flash('errorMsg', '该链接已过期');

                // 置为已失效
                $verify->status = 2;
                $verify->save();

                // 重新获取一遍verify
                $view['verify'] = Verify::query()->where('type', 1)->where('token', $token)->with('user')->first();

                return Response::view('auth.reset', $view);
            }

            $view['verify'] = $verify;

            return Response::view('auth.reset', $view);
        }
    }

    // 激活账号页
    public function activeUser(Request $request)
    {
        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));

            // 是否开启账号激活
            if (!self::$systemConfig['is_active_register']) {
                Session::flash('errorMsg', '系统未开启账号激活功能，请联系管理员');

                return Redirect::back()->withInput();
            }

            // 查找账号
            $user = User::query()->where('username', $username)->first();
            if (!$user) {
                Session::flash('errorMsg', '账号不存在，请重试');

                return Redirect::back();
            } elseif ($user->status < 0) {
                Session::flash('errorMsg', '账号已禁止登陆，无需激活');

                return Redirect::back();
            } elseif ($user->status > 0) {
                Session::flash('errorMsg', '账号无需激活');

                return Redirect::back();
            }

            // 24小时内激活次数限制
            $activeTimes = 0;
            if (Cache::has('activeUser_' . md5($username))) {
                $activeTimes = Cache::get('activeUser_' . md5($username));
                if ($activeTimes >= self::$systemConfig['active_times']) {
                    Session::flash('errorMsg', '同一个账号24小时内只能请求激活' . self::$systemConfig['active_times'] . '次，请勿频繁操作');

                    return Redirect::back();
                }
            }

            // 生成激活账号的地址
            $token = md5(self::$systemConfig['website_name'] . $username . microtime());
            $verify = new Verify();
            $verify->type = 1;
            $verify->user_id = $user->id;
            $verify->token = $token;
            $verify->status = 0;
            $verify->save();

            // 发送邮件
            $activeUserUrl = self::$systemConfig['website_url'] . '/active/' . $token;
            $title = '重新激活账号';
            $content = '请求地址：' . $activeUserUrl;

            try {
                Mail::to($username)->send(new activeUser(self::$systemConfig['website_name'], $activeUserUrl));
                Helpers::addEmailLog($username, $title, $content);
            } catch (\Exception $e) {
                Helpers::addEmailLog($username, $title, $content, 0, $e->getMessage());
            }

            Cache::put('activeUser_' . md5($username), $activeTimes + 1, 1440);
            Session::flash('successMsg', '激活邮件已发送，如未收到，请查看垃圾箱');

            return Redirect::back();
        } else {
            return Response::view('auth.activeUser');
        }
    }

    // 激活账号
    public function active(Request $request, $token)
    {
        if (empty($token)) {
            return Redirect::to('login');
        }

        $verify = Verify::query()->where('type', 1)->where('token', $token)->with('user')->first();
        if (empty($verify)) {
            return Redirect::to('login');
        } elseif (empty($verify->user)) {
            Session::flash('errorMsg', '该链接已失效');

            return Response::view('auth.active');
        } elseif ($verify->status == 1) {
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
        $username = trim($request->get('username'));

        if (!$username) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '请输入用户名']);
        }

        // 校验账号合法性
        if (false === filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '用户名必须是合法邮箱，请重新输入']);
        }

        $user = User::query()->where('username', $username)->first();
        if ($user) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '用户已存在，无需注册，如果忘记密码请找回密码']);
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
        $title = '发送注册验证码';
        $content = '验证码：' . $code;

        try {
            Mail::to($username)->send(new sendVerifyCode($code));
            Helpers::addEmailLog($username, $title, $content);
        } catch (\Exception $e) {
            Helpers::addEmailLog($username, $title, $content, 0, $e->getMessage());
        }

        $this->addVerifyCode($username, $code);

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
        // 解析IP信息
        $qqwry = new QQWry();
        $ipInfo = $qqwry->ip($ip);
        if (isset($ipInfo['error'])) {
            Log::info('无法识别IP，可能是IPv6，尝试解析：' . $ip);
            $ipInfo = getIPv6($ip);
        }

        if (empty($ipInfo) || empty($ipInfo['country'])) {
            Log::warning("获取IP地址信息异常：" . $ip);
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
     * @param string $aff  URL中的aff参数
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