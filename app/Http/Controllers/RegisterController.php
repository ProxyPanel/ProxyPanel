<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Http\Models\Invite;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\Verify;
use Illuminate\Http\Request;
use App\Mail\activeUser;
use Captcha;
use Response;
use Redirect;
use Session;
use Cache;
use Mail;

/**
 * 注册控制器
 * Class LoginController
 *
 * @package App\Http\Controllers
 */
class RegisterController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    // 注册页
    // TODO：改成点击发送验证码按钮，而不是到邮箱里去打开激活链接
    public function index(Request $request)
    {
        $cacheKey = 'register_times_' . md5(getClientIp()); // 注册限制缓存key

        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));
            $password = trim($request->get('password'));
            $repassword = trim($request->get('repassword'));
            $captcha = trim($request->get('captcha'));
            $code = trim($request->get('code'));
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

            // 是否校验验证码
            if (self::$systemConfig['is_captcha']) {
                if (!Captcha::check($captcha)) {
                    Session::flash('errorMsg', '验证码错误，请重新输入');

                    return Redirect::back()->withInput();
                }
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
            $user->password = md5($password);
            $user->port = $port;
            $user->passwd = makeRandStr();
            $user->transfer_enable = $transfer_enable;
            $user->method = Helpers::getDefaultMethod();
            $user->protocol = Helpers::getDefaultProtocol();
            $user->obfs = Helpers::getDefaultObfs();
            $user->enable_time = date('Y-m-d H:i:s');
            $user->expire_time = date('Y-m-d H:i:s', strtotime("+" . self::$systemConfig['default_days'] . " days"));
            $user->reg_ip = getClientIp();
            $user->referral_uid = $referral_uid;
            $user->save();

            if ($user->id) {
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
            }

            // 发送邮件
            if (self::$systemConfig['is_active_register']) {
                // 生成激活账号的地址
                $token = md5(self::$systemConfig['website_name'] . $username . microtime());
                $activeUserUrl = self::$systemConfig['website_url'] . '/active/' . $token;
                $this->addVerify($user->id, $username, $token);

                try {
                    Mail::to($username)->send(new activeUser(self::$systemConfig['website_name'], $activeUserUrl));
                    $this->sendEmailLog($user->id, '注册激活', '请求地址：' . $activeUserUrl);
                } catch (\Exception $e) {
                    $this->sendEmailLog($user->id, '注册激活', '请求地址：' . $activeUserUrl, 0, $e->getMessage());
                }

                Session::flash('regSuccessMsg', '注册成功：激活邮件已发送，如未收到，请查看垃圾邮箱');
            } else {
                // 如果不需要激活，则直接给推荐人加流量
                if ($referral_uid) {
                    $transfer_enable = self::$systemConfig['referral_traffic'] * 1048576;

                    User::query()->where('id', $referral_uid)->increment('transfer_enable', $transfer_enable);
                    User::query()->where('id', $referral_uid)->update(['enable' => 1]);
                }

                Session::flash('regSuccessMsg', '注册成功');
            }

            return Redirect::to('login');
        } else {
            Session::put('register_token', makeRandStr(16));

            $view['is_captcha'] = self::$systemConfig['is_captcha'];
            $view['is_register'] = self::$systemConfig['is_register'];
            $view['website_home_logo'] = self::$systemConfig['website_home_logo'];
            $view['is_invite_register'] = self::$systemConfig['is_invite_register'];
            $view['is_free_code'] = self::$systemConfig['is_free_code'];
            $view['website_analytics'] = self::$systemConfig['website_analytics'];
            $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

            return Response::view('register', $view);
        }
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

        // 有邀请码先用邀请码，用谁的邀请码就给谁返利
        if ($code) {
            $inviteCode = Invite::query()->where('code', $code)->where('uid', '>', 0)->where('status', 0)->first();
            if ($inviteCode) {
                $referral_uid = $inviteCode->uid;

                return [
                    'referral_uid' => $referral_uid,
                    'code_id'      => $inviteCode->id
                ];
            }
        }

        // 没有用邀请码或者邀请码是管理员生成的，则检查cookie或者url链接
        if (!$referral_uid) {
            // 检查一下cookie里有没有aff
            $cookieAff = \Request::cookie('register_aff') ? \Request::cookie('register_aff') : 0;
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
            'code_id'      => 0
        ];
    }

    // 写入生成激活账号验证记录
    private function addVerify($userId, $username, $token)
    {
        $verify = new Verify();
        $verify->user_id = $userId;
        $verify->username = $username;
        $verify->token = $token;
        $verify->status = 0;
        $verify->save();
    }
}
