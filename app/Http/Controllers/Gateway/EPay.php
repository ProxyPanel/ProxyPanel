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
			'pid'          => self::$sysConfig['epay_mch_id'],
			'type'         => $type,
			'notify_url'   => (self::$sysConfig['website_callback_url']?: self::$sysConfig['website_url']).'/callback/notify?method=epay',
			'return_url'   => self::$sysConfig['website_url'].'/invoices',
			'out_trade_no' => $payment->trade_no,
			'name'         => self::$sysConfig['subject_name']?: self::$sysConfig['website_name'],
			'money'        => $payment->amount,
			'sign_type'    => 'MD5'
		];
		$data['sign'] = $this->aliStyleSign($data, self::$sysConfig['epay_key']);

		$url = self::$sysConfig['epay_url'].'submit.php?'.http_build_query($data);
		Payment::whereId($payment->id)->update(['url' => $url]);

		return Response::json(['status' => 'success', 'url' => $url, 'message' => '创建订单成功!']);
	}

	public function notify(Request $request): void {
		if($request->input('trade_status') === 'TRADE_SUCCESS'
		   && $this->verify($request->except('method'), self::$sysConfig['epay_key'], $request->input('sign'))){
			$this->postPayment($request->input('out_trade_no'), 'EPay');
			exit('SUCCESS');
		}
		exit('FAIL');
	}

	public function queryInfo(): JsonResponse {
		$request = (new Client())->get(self::$sysConfig['epay_url'].'api.php', [
			'query' => [
				'act' => 'query',
				'pid' => self::$sysConfig['epay_mch_id'],
				'key' => self::$sysConfig['epay_key']
			]
		]);
		if($request->getStatusCode() == 200){
			return Response::json(['status' => 'success', 'data' => json_decode($request->getBody(), true)]);
		}

		return Response::json(['status' => 'fail', 'message' => '获取失败！请检查配置信息']);
	}
}
