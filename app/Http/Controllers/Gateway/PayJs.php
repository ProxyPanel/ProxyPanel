<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Models\Payment;
use Auth;
use Log;
use Response;
use Xhat\Payjs\Payjs as Pay;

class PayJs extends AbstractPayment
{
	private static $config;

	function __construct()
	{
		parent::__construct();
		self::$config = [
			'mchid' => self::$systemConfig['payjs_mch_id'],   // 配置商户号
			'key'   => self::$systemConfig['payjs_key'],   // 配置通信密钥
		];
	}

	public function purchase($request)
	{
		$payment = new Payment();
		$payment->sn = self::generateGuid();
		$payment->user_id = Auth::user()->id;
		$payment->oid = $request->input('oid');
		$payment->amount = $request->input('amount');
		$payment->save();

		$result = (new Pay($this::$config))->native([
			'body'         => parent::$systemConfig['subject_name']? : parent::$systemConfig['website_name'],
			'total_fee'    => $payment->amount*100,
			'out_trade_no' => $payment->sn,
			'attach'       => '',
			'notify_url'   => (parent::$systemConfig['website_callback_url']? : parent::$systemConfig['website_url']).'/callback/notify?method=payjs',
		]);

		if(!$result->return_code){
			Log::error('PayJs '.$result->return_msg);
		}
		$payment->qr_code = $result->qrcode;// 获取收款二维码内容
		$payment->save();

		return Response::json(['status' => 'success', 'data' => $payment->sn, 'message' => '创建订单成功!']);
	}



	public function notify($request)
	{
		$data = (new Pay($this::$config))->notify();

		if($data['return_code'] == 1){
			$this::postPayment($data['out_trade_no'], 'PayJs');
			exit("success");
		}
		exit("fail");
	}


	public function getReturnHTML($request)
	{
		// TODO: Implement getReturnHTML() method.
	}


	public function getPurchaseHTML()
	{
		// TODO: Implement getReturnHTML() method.
	}
}