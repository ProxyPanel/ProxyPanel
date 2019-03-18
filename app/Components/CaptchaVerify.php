<?php

namespace App\Components;

use Illuminate\Http\Request;

/**
 * Class CaptchaVerify 验证码
 *
 * @package App\Components
 */

Class CaptchaVerify 
{
    /**
     * 从后台获取 Geetest_id 和 Geetest_key
     */
    public static function geetestGetKey() 
    {
        return [
            "geetest_id" => Helpers::systemConfig()["geetest_id"],
            "geetest_key" => Helpers::systemConfig()["geetest_key"]
        ];
    }
}

?>