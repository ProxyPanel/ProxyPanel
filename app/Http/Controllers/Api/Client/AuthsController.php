<?php

namespace App\Http\Controllers\Api;


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

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
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

}