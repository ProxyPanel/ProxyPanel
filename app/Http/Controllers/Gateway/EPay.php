<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class EPay extends AbstractPayment {
	// Todo Debug测试
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
			'sign_type'    => 'MD5'
		];
		$data['sign'] = $this->sign($this->prepareSign($data));

		$client = new Client(['timeout' => 5]);
		$request = $client->get(self::$systemConfig['epay_url'].'/submit.php');
		$result = json_decode($request->getBody(), true);

		if($request->getStatusCode() != 200){
			return Response::json(['status' => 'fail', 'message' => '网关处理失败!']);
		}

		if(!$result){
			return Response::json(['status' => 'fail', 'message' => '支付处理失败!']);
		}

		Payment::whereId($payment->id)->update(['qr_code' => 1, 'url' => $result['pay_url']]);

		return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
	}

	// 签名字符串
	private function sign($data): string {
		unset($data['sign'], $data['sign_type']);
		array_filter($data);
		ksort($data);
		reset($data);

		return md5(urldecode(http_build_query($data).self::$systemConfig['epay_key']));
	}

	public function notify(Request $request): void {
		if($this->verify($request->except('method'), $request->input('sign'))
		   && $request->input('trade_status') == 'TRADE_SUCCESS'){
			$this->postPayment($request->input('out_trade_no'), 'EPay');
			die('SUCCESS');
		}
		die('FAIL');
	}

	// 验证签名
	private function verify($data, $signature): bool {
		return $this->sign($data) === $signature;
	}

	public function queryInfo(): JsonResponse {
		$request = self::$client->get('api.php', [
			'query' => [
				'act' => 'query',
				'pid' => self::$systemConfig['epay_mch_id'],
				'key' => self::$systemConfig['epay_key']
			]
		]);
		if($request->getStatusCode() == 200){
			return Response::json(['status' => 'success', 'data' => json_decode($request->getBody(), true)]);
		}

		return Response::json(['status' => 'fail', 'message' => '获取失败！请检查配置信息']);
	}
}
