<?php

namespace App\Components;

/**
 * Class CaptchaVerify 验证码
 *
 * @package App\Components
 */
class CaptchaVerify
{
    //从后台获取 hcaptcha_sitekey 和 hcaptcha_secret
    public static function hCaptchaGetConfig(): array
    {
        return [
            "sitekey" => sysConfig('hcaptcha_sitekey'),
            "secret"  => sysConfig('hcaptcha_secret'),
            "options" => [],
        ];
    }

    //从后台获取 Geetest_id 和 Geetest_key
    public static function geetestCaptchaGetConfig(): array
    {
        return [
            "geetest_id"  => sysConfig('geetest_id'),
            "geetest_key" => sysConfig('geetest_key'),
        ];
    }

    //从后台获取 google_captcha_sitekey 和 google_captcha_secret
    public static function googleCaptchaGetConfig(): array
    {
        return [
            "sitekey" => sysConfig('google_captcha_sitekey'),
            "secret"  => sysConfig('google_captcha_secret'),
            "options" => [],
        ];
    }
}
