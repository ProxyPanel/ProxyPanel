<?php

namespace App\Components;

/**
 * Class CaptchaVerify 验证码
 *
 * @package App\Components
 */
class CaptchaVerify {
	//从后台获取 hcaptcha_sitekey 和 hcaptcha_secret
	public static function hCaptchaGetConfig() {
		return [
			"sitekey" => Helpers::systemConfig()["hcaptcha_sitekey"],
			"secret"  => Helpers::systemConfig()["hcaptcha_secret"],
			"options" => []
		];
	}

	//从后台获取 Geetest_id 和 Geetest_key
	public static function geetestCaptchaGetConfig() {
		return [
			"geetest_id"  => Helpers::systemConfig()["geetest_id"],
			"geetest_key" => Helpers::systemConfig()["geetest_key"]
		];
	}

	//从后台获取 google_captcha_sitekey 和 google_captcha_secret
	public static function googleCaptchaGetConfig() {
		return [
			"sitekey" => Helpers::systemConfig()["google_captcha_sitekey"],
			"secret"  => Helpers::systemConfig()["google_captcha_secret"],
			"options" => []
		];
	}
}

?>
