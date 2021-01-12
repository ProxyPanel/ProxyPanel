<?php

namespace App\Http\Controllers\Api\Client;

use App\Components\IP;
use App\Components\Helpers;
use App\Components\IPIP;
use App\Components\QQWry;
use App\Models\Invite;
use App\Models\User;
use App\Models\UserLoginLog;
use App\Models\UserLabel;
use App\Models\UserSubscribe;
use App\Models\Verify;
use App\Models\VerifyCode;
use App\Mail\activeUser;
use App\Mail\resetPassword;
use App\Mail\sendVerifyCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Response;
use Redirect;
use Captcha;
use Session;
use Cache;
use Auth;
use Mail;
use Hash;
use Log;
use Illuminate\Support\Facades\Validator;
/**
 * 认证控制器
 *
 * Class AuthController
 *
 * @package App\Http\Controllers
 */
class AuthsController extends Controller
{
    protected static $systemConfig;
    public $new_username='';

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }
    
    // 自动注册
    
   
	
    public function autoRegister(Request $request)
    {
           $cacheKey = 'register_times_'.md5(IP::getClientIp()); // 注册限制缓存key

           	$password_str = Str::random(10);
          

           //更新用户名
            $is_not_new = true;
            while ($is_not_new) {
                $is_not_new = $this->get_qnique_email();
            }


            $username = $this->new_username;
            $email = $username;
            $password = Hash::make($password_str);
            $appkey = $request->input('appkey');
            $aff = (int) $request->input('aff');

           

            // 是否开启注册
            if (! sysConfig('is_register')) {
                Session::flash('errorRegMsg', '系统维护，暂停注册');
                return Redirect::back()->withErrors(trans('auth.register_close'));
            }

            // 校验域名邮箱黑白名单
            if (sysConfig('is_email_filtering')) {
                $result = $this->emailChecker($email, 1);
                if ($result !== false) {
                    return $result;
                }
            }

            
           

          

            // 获取可用端口
            $port = Helpers::getPort();
            if ($port > sysConfig('max_port')) {
                return Redirect::back()->withInput()->withErrors(trans('auth.register_close'));
            }

            // 获取aff
            $affArr = $this->getAff(10, $aff);
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
            
            $user= User::find($uid);
           //  \Log::debug($user);
            $tokenResult = $user->createToken('Personal Access Token');
           //   \Log::debug($tokenResult);
            $token = $tokenResult->token;
            
            
                $response['error_code'] = 0;
                $response['message']    = '自动注册成功';
                $response['token_data'] = [
            		'token_type'   =>  'Bearer',
                	'access_token' => $tokenResult->accessToken,
                	'expire_in'    => $tokenResult->token->expires_at,
                	'refresh_token' =>  ''
                ]; 
            
        return response()->json($response);
    }
    
    
     public function login(Request $request){ 
  	
  	 //\Log::debug($request->data);
  	 
  	  
  	
  	// \Log::debug($request->input('data.usename'));
  //	$input_usename = $request->input('data.username');
  //	$input_password = $request->input('data.password');
      if(Auth::attempt(['username' => $request->username, 'password' =>$request->password])){ 
            $user = Auth::user(); 
            $tokenResult       = $user->createToken('Personal Access Token');
            $token             = $tokenResult->token;
         //  $token->expires_at = Carbon::now()->addHours(1);
            $token->save();
        
        
            $server_data = $this->getServerList($user->id);

            $response['error_code'] = 0;
            $response['message']    = '登录成功';
            $response['token_data']       = [
                'token_type'   =>  'Bearer',
                'access_token' => $tokenResult->accessToken,
                'expire_in'    => $tokenResult->token->expires_at,
                'refresh_token' =>  ''
            ]; 
            
            $response['server_data'] = $server_data ;
            
            $response['clinet_smart_config'] = $this->getClientSmartConfig() ;
           
    		//生成新的token事件
            event(new GetNewToken($user));
           
            return response()->json( $response);
            
               
            
         
            } else {
                return Response::json(['error_code' => 3001,  'message' => '用户名或者密码错误']);
            }
    }



    public function updatePasswordWithAuth(Request $request){

        $validator = Validator::make($request->all(), [
            'new_password'      => 'required'
        ]);
        $user = User::where('id', Auth::id())->first();
        $user->password = Hash::make($request->new_password);
        $user->save();
        if ($user) {
            $response['error_code'] = 1;
            $response['message']    = '密码修改成功';
            
            return response()->json([
                'success' => $response
            ]);

        }else{
            $response['error_code'] = null;
            $response['message']    = '';
            return response()->json([
                'error' => $response
            ]);
        }
    }
    public function updatePasswordWithCode(Request $request){
        $validator = Validator::make($request->all(), [
            'username'     => 'required|email',
            'new_password' => 'required',
            'code'        => 'required'

        ]);
        if ($validator->fails()) {
            // return response()->json($validator->messages(), 422);
            $response['error_code'] = 1000;
            $response['message']    = '';

            return response()->json([
                'error' => $response
            ]);

        }


        $verifyCode = VerifyCode::query()->where('username', $request->username)->where('code', $request->code)->where('status', 0)->first();
        if ($verifyCode) {
            $user = User::where('username', $request->username)->first();
            $user->password = Hash::make($request->new_password);
            $user->save();
            if ($user) {
                $verifyCode = VerifyCode::query()->where('username', $request->username)->where('code', $request->code)->where('status', 0)->first();
                $verifyCode->status = 1; 
                $verifyCode->save(); 

                $response['error_code'] = 0;
                $response['message']    = '密码修改成功';
                
                return response()->json([
                    'success' => $response
                ]);

            }
        }else{
            $response['error_code'] = 1000;
            $response['message']    = '';
            return response()->json($response);
              
        }
    }


    public function updateUser(Request $request){
        $validator = Validator::make($request->all(), [
            'new_username'      => 'required|email|unique:user,username,'.Auth::id(),
            'new_password'      => 'required',
            'appkey'        => 'required',
            'device'        => 'required',
            'timestamp'     => 'required'

        ]);
        if ($validator->fails()) {
            // return response()->json($validator->messages(), 422);
            $response['error_code'] = 5001;
            $response['message']    = '用户名已经被使用';

            return response()->json($response);
             
        }
    
        $user = User::where('id', Auth::id())->first();
        $verifyCode = VerifyCode::query()->where('username', $user->username)->where('code', $request->code)->where('status', 0)->first();
        
        if ($user->user_type == 1 && $verifyCode) {
            $user->username  = $request->new_username;
            $user->user_type = 2;
            $user->password  = Hash::make($request->new_password);
            $user->save();
           
            if ($user) {
            	// 失效已使用的验证码，这里会有bug ，如果在这个过程当中用户再来获取验证码，会有执行错误出现。
                $verifyCode = VerifyCode::query()->where('username', $user->username)->where('code', $request->code)->where('status', 0)->first();
                $verifyCode->status = 1; 
                $verifyCode->save(); 
            	
            	
                $response['error_code'] = 0;
                $response['message']    = '升级为注册用户成功';
                $response['data']       = ['user_type' => $user->user_type]; //not clear
                return response()->json($response);
                    
            }else{
                $response['error_code'] = null;
                $response['message']    = '';

                return response()->json([
                    'error' => $response
                ]);
            }
        }else{
            $response['error_code'] = 5005;
            $response['message']    = '已经为注册用户不能再次修改或者为验证码已经失效';

            return response()->json($response);
                
            

    }


   }

   public function sendCodeAPI(Request $request)
    {
        $username = trim($request->post('username'));

        if (!$username) {
            $response['error_code'] = null;
            $response['message']    = '1';

            return response()->json([
                'error' => $response
            ]);
        }

        // 校验账号合法性
        if (false === filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $response['error_code'] = null;
            $response['message']    = '2';

            return response()->json([
                'error' => $response
            ]);
        }

        // 校验域名邮箱是否在敏感词中
        $sensitiveWords = $this->sensitiveWords();
        $usernameSuffix = explode('@', $username); // 提取邮箱后缀
        if (in_array(strtolower($usernameSuffix[1]), $sensitiveWords)) {
            $response['error_code'] = null;
            $response['message']    = '3';

            return response()->json([
                'error' => $response
            ]);
        }

        $user = User::query()->where('username', $username)->first();
        if (!$user) {
            $response['error_code'] = null;
            $response['message']    = '4';

            return response()->json([
                'error' => $response
            ]);
        }
        
         // 防刷机制
        if (Cache::has('send_verify_code_' . md5(getClientIP()))) {
            $response['error_code'] = null;
            $response['message']    = '6';

            return response()->json([
                'error' => $response
            ]);
        }

        // 发送邮件
        $code = makeRandStr(6, true);
        $title = '发送注册验证码';
        $content = '验证码：' . $code;

        $logId = Helpers::addEmailLog($username, $title, $content);
        Mail::to($username)->send(new sendVerifyCode($logId, $code));

        $this->addVerifyCode($username, $code);

        Cache::put('send_verify_code_' . md5(getClientIP()), getClientIP(), 1);

        $response['error_code'] = 0;
        $response['message']    = '验证码成功发送到邮箱';            
        return response()->json($response);


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
    
     //获取自动注册唯一的用户名
	private function get_qnique_email(){
		$this->new_username = Str::random(15).'@77cloud.com';
		if (User::where('username', $this->new_username)->count() == 0) {
			return false;	
		}
		return true;
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

}