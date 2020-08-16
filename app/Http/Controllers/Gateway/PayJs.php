<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Auth;
use Illuminate\Http\JsonResponse;
use Response;
use Xhat\Payjs\Payjs as Pay;

class PayJs extends AbstractPayment {
	private static $config;

	public function __construct() {
		parent::__construct();
		self::$config = [
			'mchid' => self::$sysConfig['payjs_mch_id'],   // 配置商户号
			'key'   => self::$sysConfig['payjs_key'],   // 配置通信密钥
		];
	}

	public function purchase($request): JsonResponse {
		$payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

		$result = (new Pay($this::$config))->cashier([
			'body'         => self::$sysConfig['subject_name']?: self::$sysConfig['website_name'],
			'total_fee'    => $payment->amount * 100,
			'out_trade_no' => $payment->trade_no,
			'notify_url'   => (self::$sysConfig['website_callback_url']?: self::$sysConfig['website_url']).'/callback/notify?method=payjs',
		]);

		// 获取收款二维码内容
		$payment->update(['qr_code' => 1, 'url' => $result]);

		//$this->addPamentCallback($payment->trade_no, null, $payment->amount * 100);
		return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
	}

	public function notify($request): void {
		$data = (new Pay($this::$config))->notify();

		if($data['return_code'] == 1){
			$payment = Payment::whereTradeNo($data['out_trade_no'])->first();
			if($payment){
				$ret = $payment->order->update(['status' => 2]);
				if($ret){
					exit('success');
				}
			}
		}
		exit('fail');
	}
}
