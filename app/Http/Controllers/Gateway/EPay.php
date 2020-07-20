<?php

namespace App\Http\Controllers\Gateway;

use App\Components\Curl;
use App\Models\Payment;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class EPay extends AbstractPayment {
	public function purchase(Request $request): JsonResponse {
		$payment = $this->creatNewPayment(Auth::id(), $request->input('oid'), $request->input('amount'));

		switch($request->input('type')){
			case 2:
				$type = 'qqpay';
				break;
			case 3:
				$type = 'wxpay';
				break;
			case 1:
			default:
				$type = 'alipay';
				break;
		}


		$data = [
			'pid'          => self::$systemConfig['epay_mch_id'],
			'type'         => $type,
			'out_trade_no' => $payment->trade_no,
			'notify_url'   => (self::$systemConfig['website_callback_url']?: self::$systemConfig['website_url']).'/callback/notify?method=epay',
			'return_url'   => self::$systemConfig['website_url'].'/invoices',
			'name'         => self::$systemConfig['subject_name']?: self::$systemConfig['website_name'],
			'money'        => $payment->amount,

		];
		$data['sign'] = $this->sign($this->prepareSign($data));

		$result = json_decode(Curl::send(self::$systemConfig['epay_url'].'/submit.php', $data), true);
		if(!$result){
			return Response::json(['status' => 'fail', 'message' => '支付处理失败!']);
		}

		Payment::whereId($payment->id)->update(['qr_code' => 1, 'url' => $result['pay_url']]);

		return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
	}

	// 签名字符串
	private function sign($data): string {
		return strtolower(md5($data.self::$systemConfig['epay_key']));
	}

	private function prepareSign($data): string {
		ksort($data);
		return http_build_query($data);
	}

	public function notify(Request $request): void {

		if(!$this->verify($request->all(), $request->input('sign'))){
			die('FAIL');
		}
		$this->postPayment($request->input('out_trade_no'), 'EPay');
		die('SUCCESS');
	}

	// 验证签名
	private function verify($data, $signature): bool {
		unset($data['sign']);
		return $this->sign($this->prepareSign($data)) === $signature;
	}
}
