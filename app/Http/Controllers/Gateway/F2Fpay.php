<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Auth;
use Exception;
use InvalidArgumentException;
use Log;
use Payment\Client;
use Payment\Exceptions\ClassNotFoundException;
use Response;

class F2Fpay extends AbstractPayment {
	private static $aliConfig;

	function __construct() {
		parent::__construct();
		self::$aliConfig = [
			'use_sandbox'     => false,
			'app_id'          => self::$systemConfig['f2fpay_app_id'],
			'sign_type'       => 'RSA2',
			'ali_public_key'  => self::$systemConfig['f2fpay_public_key'],
			'rsa_private_key' => self::$systemConfig['f2fpay_private_key'],
			'limit_pay'       => [],
			'notify_url'      => (self::$systemConfig['website_callback_url']?: self::$systemConfig['website_url']).'/callback/notify?method=f2fpay',
			'return_url'      => self::$systemConfig['website_url'].'/invoices',
			'fee_type'        => 'CNY',
		];
	}

	public function purchase($request) {
		$payment = new Payment();
		$payment->trade_no = self::generateGuid();
		$payment->user_id = Auth::id();
		$payment->oid = $request->input('oid');
		$payment->amount = $request->input('amount');
		$payment->save();

		$data = [
			'body'        => '',
			'subject'     => self::$systemConfig['subject_name']?: self::$systemConfig['website_name'],
			'trade_no'    => $payment->trade_no,
			'time_expire' => time() + 900, // 必须 15分钟 内付款
			'amount'      => $payment->amount,
		];

		try{
			$client = new Client(Client::ALIPAY, self::$aliConfig);
			$result = $client->pay(Client::ALI_CHANNEL_QR, $data);
		}catch(InvalidArgumentException $e){
			Log::error("【支付宝当面付】输入信息错误: ".$e->getMessage());
			exit;
		}catch(ClassNotFoundException $e){
			Log::error("【支付宝当面付】未知类型: ".$e->getMessage());
			exit;
		}catch(Exception $e){
			Log::error("【支付宝当面付】错误: ".$e->getMessage());
			exit;
		}

		Payment::whereId($payment->id)
		       ->update(['qr_code' => 'http://qr.topscan.com/api.php?text='.$result['qr_code'].'&bg=ffffff&fg=000000&pt=1c73bd&m=10&w=400&el=1&inpt=1eabfc&logo=https://t.alipayobjects.com/tfscom/T1Z5XfXdxmXXXXXXXX.png']);//后备：https://cli.im/api/qrcode/code?text=".$result['qr_code']."&mhid=5EfGCwztyckhMHcmI9ZcOKs

		return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
	}

	public function notify($request) {
		$data = [
			'trade_no'       => $request->input('out_trade_no'),
			'transaction_id' => $request->input('trade_no'),
		];

		try{
			$client = new Client(Client::ALIPAY, self::$aliConfig);
			$result = $client->tradeQuery($data);
			Log::info("【支付宝当面付】回调验证查询：".var_export($result, true));
		}catch(InvalidArgumentException $e){
			Log::error("【支付宝当面付】回调信息错误: ".$e->getMessage());
			exit;
		}catch(ClassNotFoundException $e){
			Log::error("【支付宝当面付】未知类型: ".$e->getMessage());
			exit;
		}catch(Exception $e){
			Log::error("【支付宝当面付】错误: ".$e->getMessage());
			exit;
		}

		$ret = "fail";
		if($result['code'] == 10000 && $result['msg'] == "Success"){
			$ret = "success";
			if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS'){
				self::postPayment($request->input('out_trade_no'), '支付宝当面付');
			}else{
				Log::info('支付宝当面付-POST:交易失败['.getClientIp().']');
			}
		}else{
			Log::info('支付宝当面付-POST:验证失败['.getClientIp().']');
		}

		// 返回验证结果
		exit($ret);
	}

	public function getReturnHTML($request) {
		// TODO: Implement getReturnHTML() method.
	}

	public function getPurchaseHTML() {
		// TODO: Implement getReturnHTML() method.
	}
}
