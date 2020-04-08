<?php

namespace App\Http\Controllers\Api;

use App\Components\Callback;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;
use Xhat\Payjs\Payjs;

class PayJsController extends Controller
{
	use Callback;

	// 接收GET请求
	public function index(Request $request)
	{
		Log::info("【PayJs】回调接口[GET]：".var_export($request->all(), TRUE).'['.getClientIp().']');
		exit("【PayJs】接口正常");
	}

	// 接收POST请求
	public function store(Request $request)
	{
		Log::info("【PayJs】回调接口[POST]：".var_export($request->all(), TRUE));
		$config = [
			'mchid' => self::$systemConfig['payjs_mch_id'],
			'key'   => self::$systemConfig['payjs_key'],
		];

		// 初始化
		$payjs = new Payjs($config);
		$notify_info = $payjs->notify();

		// 使用

		$result = "fail";
		if($notify_info['return_code'] == 1){ // 验证成功
			$result = "success";
			// 商户订单号
			$data = [];
			$data['out_trade_no'] = $request->input('out_trade_no');
			// 接口交易号
			$data['trade_no'] = $request->input('payjs_order_id');
			// 交易状态
			$data['trade_status'] = $request->input('return_code');
			// 交易金额(这里是按照结算货币汇率的金额，和rmb_fee不相等)
			$data['total_amount'] = $request->input('total_fee');

			$this->tradePaid($data, 6);
		}else{
			Log::info('PayJs-POST:验证失败['.getClientIp().']');
		}

		// 返回验证结果
		exit($result);
	}
}
