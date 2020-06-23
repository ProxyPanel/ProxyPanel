<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Auth;
use Log;
use Response;
use Xhat\Payjs\Payjs as Pay;

class PayJs extends AbstractPayment {
	private static $config;

	function __construct() {
		parent::__construct();
		self::$config = [
			'mchid' => self::$systemConfig['payjs_mch_id'],   // 配置商户号
			'key'   => self::$systemConfig['payjs_key'],   // 配置通信密钥
		];
	}

	public function purchase($request) {
		$payment = new Payment();
		$payment->trade_no = self::generateGuid();
		$payment->user_id = Auth::id();
		$payment->oid = $request->input('oid');
		$payment->amount = $request->input('amount');
		$payment->save();

		$result = (new Pay($this::$config))->native([
			'body'         => parent::$systemConfig['subject_name']?: parent::$systemConfig['website_name'],
			'total_fee'    => $payment->amount * 100,
			'out_trade_no' => $payment->trade_no,
			'attach'       => '',
			'notify_url'   => (parent::$systemConfig['website_callback_url']?: parent::$systemConfig['website_url']).'/callback/notify?method=payjs',
		]);

		if($result['return_code'] != 1){
			Log::error('PayJs '.$result['return_msg']);
		}
		// 获取收款二维码内容
		Payment::whereId($payment->id)->update(['qr_code' => $result['qrcode']]);

		return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
	}

	public function notify($request) {
		$data = (new Pay($this::$config))->notify();

		if($data['return_code'] == 1){
			$this::postPayment($data['out_trade_no'], 'PayJs');
			exit("success");
		}
		exit("fail");
	}
}
