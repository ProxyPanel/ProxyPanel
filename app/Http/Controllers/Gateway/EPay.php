<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Auth;
use GuzzleHttp\Client;
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
			'notify_url'   => (self::$systemConfig['website_callback_url']?: self::$systemConfig['website_url']).'/callback/notify?method=epay',
			'return_url'   => self::$systemConfig['website_url'].'/invoices',
			'out_trade_no' => $payment->trade_no,
			'name'         => self::$systemConfig['subject_name']?: self::$systemConfig['website_name'],
			'money'        => $payment->amount,
			'sign_type'    => 'MD5'
		];
		$data['sign'] = $this->aliStyleSign($data, self::$systemConfig['epay_key']);

		$url = self::$systemConfig['epay_url'].'submit.php?'.http_build_query($data);
		Payment::whereId($payment->id)->update(['url' => $url]);

		return Response::json(['status' => 'success', 'url' => $url, 'message' => '创建订单成功!']);
	}

	public function notify(Request $request): void {
		if($request->input('trade_status') === 'TRADE_SUCCESS'
		   && $this->verify($request->except('method'), self::$systemConfig['epay_key'], $request->input('sign'))){
			$this->postPayment($request->input('out_trade_no'), 'EPay');
			exit('SUCCESS');
		}
		exit('FAIL');
	}

	public function queryInfo(): JsonResponse {
		$request = (new Client())->get(self::$systemConfig['epay_url'].'api.php', [
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
