<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use Geetest;

/**
 * 验证码控制器
 * Class CaptchaController

 * @package App\Http\Controllers
 */
class CaptchaController extends Controller 
{
    /**
     * @param Request $request
     */
    public function geetestPostValidate(Request $request)
    {   
        $result = $this->validate($request, [
            'geetest_challenge' => 'required|geetest'
        ], [
            'geetest' => config('geetest.server_fail_alert')
        ]);
        
        if ($result) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '服务器二次校验通过']);
        }
    }
} 