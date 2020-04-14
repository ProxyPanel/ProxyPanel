<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\IPIP;
use App\Components\QQWry;
use App\Http\Models\Invite;
use App\Http\Models\SensitiveWords;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\UserLoginLog;
use App\Http\Models\UserSubscribe;
use App\Http\Models\Verify;
use App\Http\Models\VerifyCode;
use App\Mail\activeUser;
use App\Mail\resetPassword;
use App\Mail\sendVerifyCode;
use Auth;
use Cache;
use Captcha;
use Cookie;
use Hash;
use Illuminate\Http\Request;
use Log;
use Mail;
use Redirect;
use Response;
use Session;
use Validator;

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
		if($request->isMethod('POST')){
			$this->validate($request, [
				'email'    => 'required',
				'password' => 'required'
			], [
				'email.required'    => trans('auth.email_null'),
				'password.required' => trans('auth.password_null')
			]);

			$email = $request->input('email');
			$password = $request->input('password');
			$remember = $request->input('remember');

			// 是否校验验证码
			$captcha = $this->check_captcha($request);
			if($captcha != FALSE){
				return $captcha;
			}

			// 验证账号并创建会话
			if(!Auth::attempt(['email' => $email, 'password' => $password], $remember)){
				return Redirect::back()->withInput()->withErrors(trans('auth.login_error'));
			}

			// 校验普通用户账号状态
			if(!Auth::user()->is_admin){
				if(Auth::user()->status < 0){
					Auth::logout(); // 强制销毁会话，因为Auth::attempt的时候会产生会话

					return Redirect::back()->withInput()->withErrors(trans('auth.login_ban', ['email' => self::$systemConfig['webmaster_email']]));
				}elseif(Auth::user()->status == 0 && self::$systemConfig['is_activate_account']){
					Auth::logout(); // 强制销毁会话，因为Auth::attempt的时候会产生会话

					return Redirect::back()->withInput()->withErrors(trans('auth.active_tip').'<a href="/activeUser?email='.$email.'" target="_blank"><span style="color:#000">【'.trans('auth.active_account').'】</span></a>');
				}
			}

			// 写入登录日志
			$this->addUserLoginLog(Auth::user()->id, getClientIp());

			// 更新登录信息
			User::uid()->update(['last_login' => time()]);

			// 根据权限跳转
			if(Auth::user()->is_admin){
				return Redirect::to('admin');
			}

			return Redirect::to('/');
		}else{
			if(Auth::check()){
				if(Auth::user()->is_admin){
					return Redirect::to('admin');
				}

				return Redirect::to('/');
			}

			return Response::view('auth.login');
		}
	}

	/**
	 * 添加用户登录日志
	 *
	 * @param string $userId 用户ID
	 * @param string $ip     IP地址
	 */
	private function addUserLoginLog($userId, $ip)
	{
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
			Log::info('识别到IPv6，尝试解析：'.$ip);
			$ipInfo = getIPv6($ip);
		}else{
			$ipInfo = QQWry::ip($ip); // 通过纯真IP库解析IPv4信息
			if(isset($ipInfo['error'])){
				Log::info('无法识别IPv4，尝试使用IPIP的IP库解析：'.$ip);
				$ipip = IPIP::ip($ip);
				$ipInfo = [
					'country'  => $ipip['country_name'],
					'province' => $ipip['region_name'],
					'city'     => $ipip['city_name']
				];
			}else{
				// 判断纯真IP库获取的国家信息是否与IPIP的IP库获取的信息一致，不一致则用IPIP的（因为纯真IP库的非大陆IP准确率较低）
				$ipip = IPIP::ip($ip);
				if($ipInfo['country'] != $ipip['country_name']){
					$ipInfo['country'] = $ipip['country_name'];
					$ipInfo['province'] = $ipip['region_name'];
					$ipInfo['city'] = $ipip['city_name'];
				}
			}
		}

		if(empty($ipInfo) || empty($ipInfo['country'])){
			Log::warning("获取IP信息异常：".$ip);
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

	// 校验验证码
	private function check_captcha($request)
	{
		switch(self::$systemConfig['is_captcha']){
			case 1: // 默认图形验证码
				if(!Captcha::check($request->input('captcha'))){
					return Redirect::back()->withInput()->withErrors(trans('auth.captcha_error'));
				}
				break;
			case 2: // Geetest
				$result = $this->validate($request, [
					'geetest_challenge' => 'required|geetest'
				], [
					'geetest' => trans('auth.captcha_fail')
				]);

				if(!$result){
					return Redirect::back()->withInput()->withErrors(trans('auth.fail_captcha'));
				}
				break;
			case 3: // Google reCAPTCHA
				$result = $this->validate($request, [
					'g-recaptcha-response' => 'required|NoCaptcha'
				]);

				if(!$result){
					return Redirect::back()->withInput()->withErrors(trans('auth.fail_captcha'));
				}
				break;
			case 4: // hCaptcha
				$result = $this->validate($request, [
					'h-captcha-response' => 'required|HCaptcha'
				]);

				if(!$result){
					return Redirect::back()->withInput()->withErrors(trans('auth.fail_captcha'));
				}
				break;
			default: // 不启用验证码
				break;
		}
	}


	// 退出
	public function logout()
	{
		Auth::logout();

		return Redirect::to('login');
	}

	// 注册
	public function register(Request $request)
	{
		$cacheKey = 'register_times_'.md5(getClientIp()); // 注册限制缓存key

		if($request->isMethod('POST')){
			$this->validate($request, [
				'username'        => 'required',
				'email'           => 'required|email|unique:user',
				'password'        => 'required|min:6',
				'confirmPassword' => 'required|same:password',
				'term'            => 'accepted'
			], [
				'username.required'        => trans('auth.email_null'),
				'email.required'           => trans('auth.email_null'),
				'email.email'              => trans('auth.email_legitimate'),
				'email.unique'             => trans('auth.email_exist'),
				'password.required'        => trans('auth.password_null'),
				'password.min'             => trans('auth.password_limit'),
				'confirmPassword.required' => trans('auth.confirm_password'),
				'confirmPassword.same'     => trans('auth.password_same'),
				'term.accepted'            => trans('auth.unaccepted')
			]);

			$username = $request->input('username');
			$email = $request->input('email');
			$password = $request->input('password');
			$register_token = $request->input('register_token');
			$code = $request->input('code');
			$verify_code = $request->input('verify_code');
			$aff = intval($request->input('aff'));

			// 防止重复提交
			if($register_token != Session::get('register_token')){
				return Redirect::back()->withInput()->withErrors(trans('auth.repeat_request'));
			}else{
				Session::forget('register_token');
			}

			// 是否开启注册
			if(!self::$systemConfig['is_register']){
				return Redirect::back()->withErrors(trans('auth.register_close'));
			}

			// 校验域名邮箱黑白名单
			if(self::$systemConfig['is_email_filtering']){
				$result = $this->emailChecker($email);
				if($result != FALSE){
					return $result;
				}
			}

			// 如果需要邀请注册
			if(self::$systemConfig['is_invite_register']){
				// 必须使用邀请码
				if(self::$systemConfig['is_invite_register'] == 2 && !$code){
					return Redirect::back()->withInput()->withErrors(trans('auth.code_null'));
				}

				// 校验邀请码合法性
				if($code){
					$codeEnable = Invite::query()->where('code', $code)->where('status', 0)->doesntExist();
					if($codeEnable){
						return Redirect::back()->withInput($request->except(['code']))->withErrors(trans('auth.code_error'));
					}
				}
			}

			// 注册前发送激活码
			if(self::$systemConfig['is_activate_account'] == 1){
				if(!$verify_code){
					return Redirect::back()->withInput($request->except(['verify_code']))->withErrors(trans('auth.captcha_null'));
				}else{
					$verifyCode = VerifyCode::query()->where('address', $email)->where('code', $verify_code)->where('status', 0)->firstOrFail();
					if(!$verifyCode){
						return Redirect::back()->withInput($request->except(['verify_code']))->withErrors(trans('auth.captcha_overtime'));
					}

					$verifyCode->status = 1;
					$verifyCode->save();
				}
			}

			// 是否校验验证码
			$captcha = $this->check_captcha($request);
			if($captcha != FALSE){
				return $captcha;
			}

			// 24小时内同IP注册限制
			if(self::$systemConfig['register_ip_limit']){
				if(Cache::has($cacheKey)){
					$registerTimes = Cache::get($cacheKey);
					if($registerTimes >= self::$systemConfig['register_ip_limit']){
						return Redirect::back()->withInput($request->except(['code']))->withErrors(trans('auth.register_anti'));
					}
				}
			}

			// 获取可用端口
			$port = self::$systemConfig['is_rand_port']? Helpers::getRandPort() : Helpers::getOnlyPort();
			if($port > self::$systemConfig['max_port']){
				return Redirect::back()->withInput()->withErrors(trans('auth.register_close'));
			}

			// 获取aff
			$affArr = $this->getAff($code, $aff);
			$referral_uid = $affArr['referral_uid'];

			$transfer_enable = 1048576*(self::$systemConfig['default_traffic']+($referral_uid? self::$systemConfig['referral_traffic'] : 0));

			// 创建新用户
			$uid = Helpers::addUser($email, Hash::make($password), $transfer_enable, self::$systemConfig['default_days'], $referral_uid);

			// 注册失败，抛出异常
			if(!$uid){
				return Redirect::back()->withInput()->withErrors(trans('auth.register_fail'));
			}
			// 更新昵称
			User::query()->whereKey($uid)->update(['username' => $username]);

			// 生成订阅码
			$subscribe = new UserSubscribe();
			$subscribe->user_id = $uid;
			$subscribe->code = Helpers::makeSubscribeCode();
			$subscribe->times = 0;
			$subscribe->save();

			// 注册次数+1
			if(Cache::has($cacheKey)){
				Cache::increment($cacheKey);
			}else{
				Cache::put($cacheKey, 1, 86400); // 24小时
			}

			// 初始化默认标签
			if(strlen(self::$systemConfig['initial_labels_for_user'])){
				$labels = explode(',', self::$systemConfig['initial_labels_for_user']);
				foreach($labels as $label){
					$userLabel = new UserLabel();
					$userLabel->user_id = $uid;
					$userLabel->label_id = $label;
					$userLabel->save();
				}
			}

			// 更新邀请码
			if(self::$systemConfig['is_invite_register'] && $affArr['code_id']){
				Invite::query()->where('id', $affArr['code_id'])->update(['fuid' => $uid, 'status' => 1]);
			}

			// 清除邀请人Cookie
			Cookie::unqueue('register_aff');

			// 注册后发送激活码
			if(self::$systemConfig['is_activate_account'] == 2){
				// 生成激活账号的地址
				$token = $this->addVerifyUrl($uid, $email);
				$activeUserUrl = self::$systemConfig['website_url'].'/active/'.$token;

				$logId = Helpers::addNotificationLog('注册激活', '请求地址：'.$activeUserUrl, 1, $email);
				Mail::to($email)->send(new activeUser($logId, $activeUserUrl));

				Session::flash('regSuccessMsg', trans('auth.register_active_tip'));
			}else{
				// 则直接给推荐人加流量
				if($referral_uid){
					$referralUser = User::query()->where('id', $referral_uid)->first();
					if($referralUser){
						if($referralUser->expire_time >= date('Y-m-d')){
							User::query()->where('id', $referral_uid)->increment('transfer_enable', self::$systemConfig['referral_traffic']*1048576);
						}
					}
				}

				if(self::$systemConfig['is_activate_account'] == 1){
					User::query()->where('id', $uid)->update(['status' => 1]);
				}

				Session::flash('regSuccessMsg', trans('auth.register_success'));
			}

			return Redirect::to('login')->withInput();
		}else{
			$view['emailList'] = self::$systemConfig['is_email_filtering'] != 2? FALSE : SensitiveWords::query()->where('type', 2)->get();
			Session::put('register_token', makeRandStr(16));

			return Response::view('auth.register', $view);
		}
	}

	//邮箱检查
	private function emailChecker($email)
	{
		$sensitiveWords = $this->sensitiveWords(self::$systemConfig['is_email_filtering']);
		$emailSuffix = explode('@', $email); // 提取邮箱后缀
		switch(self::$systemConfig['is_email_filtering']){
			// 黑名单
			case 1:
				if(in_array(strtolower($emailSuffix[1]), $sensitiveWords)){
					return Response::json(['status' => 'fail', 'message' => trans('auth.email_banned')]);
				}
				break;
			//白名单
			case 2:
				if(!in_array(strtolower($emailSuffix[1]), $sensitiveWords)){
					return Response::json(['status' => 'fail', 'message' => trans('auth.email_invalid')]);
				}
				break;
			default:
				return Response::json(['status' => 'fail', 'message' => trans('auth.email_invalid')]);
		}

		return FALSE;
	}

	/**
	 * 获取AFF
	 *
	 * @param string $code 邀请码
	 * @param int    $aff  URL中的aff参数
	 *
	 * @return array
	 */
	private function getAff($code = '', $aff = NULL)
	{
		// 邀请人ID
		$referral_uid = 0;

		// 邀请码ID
		$code_id = 0;

		// 有邀请码先用邀请码，用谁的邀请码就给谁返利
		if($code){
			$inviteCode = Invite::query()->where('code', $code)->where('status', 0)->first();
			if($inviteCode){
				$referral_uid = $inviteCode->uid;
				$code_id = $inviteCode->id;
			}
		}

		// 没有用邀请码或者邀请码是管理员生成的，则检查cookie或者url链接
		if(!$referral_uid){
			// 检查一下cookie里有没有aff
			$cookieAff = \Request::hasCookie('register_aff')? \Request::cookie('register_aff') : 0;
			if($cookieAff){
				$affUser = User::query()->where('id', $cookieAff)->exists();
				$referral_uid = $affUser? $cookieAff : 0;
			}elseif($aff){ // 如果cookie里没有aff，就再检查一下请求的url里有没有aff，因为有些人的浏览器会禁用了cookie，比如chrome开了隐私模式
				$affUser = User::query()->where('id', $aff)->exists();
				$referral_uid = $affUser? $aff : 0;
			}
		}

		return [
			'referral_uid' => $referral_uid,
			'code_id'      => $code_id
		];
	}

	// 生成申请的请求地址
	private function addVerifyUrl($uid, $email)
	{
		$token = md5(self::$systemConfig['website_name'].$email.microtime());
		$verify = new Verify();
		$verify->type = 1;
		$verify->user_id = $uid;
		$verify->token = $token;
		$verify->status = 0;
		$verify->save();

		return $token;
	}

	// 重设密码页
	public function resetPassword(Request $request)
	{
		if($request->isMethod('POST')){
			// 校验请求
			$this->validate($request, [
				'email' => 'required|email'
			], [
				'email.required' => trans('auth.email_null'),
				'email.email'    => trans('auth.email_legitimate')
			]);

			$email = $request->input('email');

			// 是否开启重设密码
			if(!self::$systemConfig['is_reset_password']){
				return Redirect::back()->withErrors(trans('auth.reset_password_close', ['email' => self::$systemConfig['webmaster_email']]));
			}

			// 查找账号
			$user = User::query()->where('email', $email)->first();
			if(!$user){
				return Redirect::back()->withErrors(trans('auth.email_notExist'));
			}

			// 24小时内重设密码次数限制
			$resetTimes = 0;
			if(Cache::has('resetPassword_'.md5($email))){
				$resetTimes = Cache::get('resetPassword_'.md5($email));
				if($resetTimes >= self::$systemConfig['reset_password_times']){
					return Redirect::back()->withErrors(trans('auth.reset_password_limit', ['time' => self::$systemConfig['reset_password_times']]));
				}
			}

			// 生成取回密码的地址
			$token = $this->addVerifyUrl($user->id, $email);

			// 发送邮件
			$resetPasswordUrl = self::$systemConfig['website_url'].'/reset/'.$token;

			$logId = Helpers::addNotificationLog('重置密码', '请求地址：'.$resetPasswordUrl, 1, $email);
			Mail::to($email)->send(new resetPassword($logId, $resetPasswordUrl));

			Cache::put('resetPassword_'.md5($email), $resetTimes+1, 86400);

			return Redirect::back()->with('successMsg', trans('auth.reset_password_success_tip'));
		}else{
			return Response::view('auth.resetPassword');
		}
	}

	// 重设密码
	public function reset(Request $request, $token)
	{
		if(!$token){
			return Redirect::to('login');
		}

		if($request->isMethod('POST')){
			$this->validate($request, [
				'password'        => 'required|min:6',
				'confirmPassword' => 'required|same:password'
			], [
				'password.required'        => trans('auth.password_null'),
				'password.min'             => trans('auth.password_limit'),
				'confirmPassword.required' => trans('auth.password_null'),
				'confirmPassword.min'      => trans('auth.password_limit'),
				'confirmPassword.same'     => trans('auth.password_same'),
			]);
			$password = $request->input('password');
			// 校验账号
			$verify = Verify::type(1)->with('user')->where('token', $token)->first();
			if(!$verify){
				return Redirect::to('login');
			}elseif($verify->status == 1){
				return Redirect::back()->withErrors(trans('auth.overtime'));
			}elseif($verify->user->status < 0){
				return Redirect::back()->withErrors(trans('auth.email_banned'));
			}elseif(Hash::check($password, $verify->user->password)){
				return Redirect::back()->withErrors(trans('auth.reset_password_same_fail'));
			}

			// 更新密码
			$ret = User::query()->where('id', $verify->user_id)->update(['password' => Hash::make($password)]);
			if(!$ret){
				return Redirect::back()->withErrors(trans('auth.reset_password_fail'));
			}

			// 置为已使用
			$verify->status = 1;
			$verify->save();

			return Redirect::back()->with('successMsg', trans('auth.reset_password_new'));
		}else{
			$verify = Verify::type(1)->where('token', $token)->first();
			if(!$verify){
				return Redirect::to('login');
			}elseif(time()-strtotime($verify->created_at) >= 1800){
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
		if($request->isMethod('POST')){
			$this->validate($request, [
				'email' => 'required|email|exists:user,email'
			], [
				'email.required' => trans('auth.email_null'),
				'email.email'    => trans('auth.email_legitimate'),
				'email.exists'   => trans('auth.email_notExist')
			]);
			$email = $request->input('email');

			// 是否开启账号激活
			if(self::$systemConfig['is_activate_account'] != 2){
				return Redirect::back()->withInput()->withErrors(trans('auth.active_close', ['email' => self::$systemConfig['webmaster_email']]));
			}

			// 查找账号
			$user = User::query()->where('email', $email)->first();
			if($user->status < 0){
				return Redirect::back()->withErrors(trans('auth.login_ban', ['email' => self::$systemConfig['webmaster_email']]));
			}elseif($user->status > 0){
				return Redirect::back()->withErrors(trans('auth.email_normal'));
			}

			// 24小时内激活次数限制
			$activeTimes = 0;
			if(Cache::has('activeUser_'.md5($email))){
				$activeTimes = Cache::get('activeUser_'.md5($email));
				if($activeTimes >= self::$systemConfig['active_times']){
					return Redirect::back()->withErrors(trans('auth.active_limit', ['time' => self::$systemConfig['webmaster_email']]));
				}
			}

			// 生成激活账号的地址
			$token = $this->addVerifyUrl($user->id, $email);

			// 发送邮件
			$activeUserUrl = self::$systemConfig['website_url'].'/active/'.$token;

			$logId = Helpers::addNotificationLog('激活账号', '请求地址：'.$activeUserUrl, 1, $email);
			Mail::to($email)->send(new activeUser($logId, $activeUserUrl));

			Cache::put('activeUser_'.md5($email), $activeTimes+1, 86400);

			return Redirect::back()->with('successMsg', trans('auth.register_active_tip'));
		}else{
			return Response::view('auth.activeUser');
		}
	}

	// 激活账号
	public function active($token)
	{
		if(!$token){
			return Redirect::to('login');
		}

		$verify = Verify::type(1)->with('user')->where('token', $token)->first();
		if(!$verify){
			return Redirect::to('login');
		}elseif(empty($verify->user)){
			Session::flash('errorMsg', trans('auth.overtime'));

			return Response::view('auth.active');
		}elseif($verify->status > 0){
			Session::flash('errorMsg', trans('auth.overtime'));

			return Response::view('auth.active');
		}elseif($verify->user->status != 0){
			Session::flash('errorMsg', trans('auth.email_normal'));

			return Response::view('auth.active');
		}elseif(time()-strtotime($verify->created_at) >= 1800){
			Session::flash('errorMsg', trans('auth.overtime'));

			// 置为已失效
			$verify->status = 2;
			$verify->save();

			return Response::view('auth.active');
		}

		// 更新账号状态
		$ret = User::query()->where('id', $verify->user_id)->update(['status' => 1]);
		if(!$ret){
			Session::flash('errorMsg', trans('auth.active_fail'));

			return Redirect::back();
		}

		// 置为已使用
		$verify->status = 1;
		$verify->save();

		// 账号激活后给邀请人送流量
		if($verify->user->referral_uid){
			$transfer_enable = self::$systemConfig['referral_traffic']*1048576;

			User::query()->where('id', $verify->user->referral_uid)->increment('transfer_enable', $transfer_enable, ['enable' => 1]);
		}

		Session::flash('successMsg', trans('auth.active_success'));

		return Response::view('auth.active');
	}

	// 发送注册验证码
	public function sendCode(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'email' => 'required|email|unique:user'
		], [
			'email.required' => trans('auth.email_null'),
			'email.email'    => trans('auth.email_legitimate'),
			'email.unique'   => trans('auth.email_exist')
		]);

		$email = $request->input('email');

		if($validator->fails()){
			return Response::json(['status' => 'fail', 'message' => $validator->getMessageBag()->first()]);
		}

		// 校验域名邮箱黑白名单
		if(self::$systemConfig['is_email_filtering']){
			$result = $this->emailChecker($email);
			if($result != FALSE){
				return $result;
			}
		}

		// 是否开启注册发送验证码
		if(self::$systemConfig['is_activate_account'] != 1){
			return Response::json(['status' => 'fail', 'message' => trans('auth.captcha_close')]);
		}

		// 防刷机制
		if(Cache::has('send_verify_code_'.md5(getClientIP()))){
			return Response::json(['status' => 'fail', 'message' => trans('auth.register_anti')]);
		}

		// 发送邮件
		$code = makeRandStr(6, TRUE);
		$logId = Helpers::addNotificationLog('发送注册验证码', '验证码：'.$code, 1, $email);
		Mail::to($email)->send(new sendVerifyCode($logId, $code));

		$this->addVerifyCode($email, $code);

		Cache::put('send_verify_code_'.md5(getClientIP()), getClientIP(), 60);

		return Response::json(['status' => 'success', 'message' => trans('auth.captcha_send')]);
	}

	// 生成注册验证码
	private function addVerifyCode($email, $code)
	{
		$verify = new VerifyCode();
		$verify->address = $email;
		$verify->code = $code;
		$verify->status = 0;
		$verify->save();
	}

	// 公开的邀请码列表
	public function free()
	{
		$view['inviteList'] = Invite::query()->where('uid', 0)->where('status', 0)->paginate();

		return Response::view('auth.free', $view);
	}

	// 切换语言
	public function switchLang($locale)
	{
		Session::put("locale", $locale);

		return Redirect::back();
	}
}