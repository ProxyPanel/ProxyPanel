<?php

namespace App\Http\Controllers\Api;

use App\Components\AlipayNotify;
use App\Components\Callback;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;

class AlipayController extends Controller
{
	use Callback;

	// 接收GET请求
	public function index(Request $request)
	{
		Log::info("【支付宝国际】回调接口[GET]：".var_export($request->all(), TRUE).'['.getClientIp().']');
		exit("【支付宝国际】接口正常");
	}

	// 接收POST请求
	public function store(Request $request)
	{
		Log::info("【支付宝国际】回调接口[POST]：".var_export($request->all(), TRUE));

		$alipayNotify = new AlipayNotify(self::$systemConfig['alipay_sign_type'], self::$systemConfig['alipay_partner'], self::$systemConfig['alipay_key'], self::$systemConfig['alipay_private_key'], self::$systemConfig['alipay_public_key'], self::$systemConfig['alipay_transport']);

		// 验证支付宝交易
		$result = "fail";
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result){ // 验证成功
			$result = "success";
			if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS'){
				// 商户订单号
				$data = [];
				$data['out_trade_no'] = $request->input('out_trade_no');
				// 支付宝交易号
				$data['trade_no'] = $request->input('trade_no');
				// 交易状态
				$data['trade_status'] = $request->input('trade_status');
				// 交易金额(这里是按照结算货币汇率的金额，和rmb_fee不相等)
				$data['total_fee'] = $request->input('total_fee');

				$this->tradePaid($data, 4);
			}else{
				Log::info('支付宝国际-POST:交易失败['.getClientIp().']');
			}
		}else{
			Log::info('支付宝国际-POST:验证失败['.getClientIp().']');
		}

		// 返回验证结果
		exit($result);
	}
}
