<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Log;
use Response;

class BitpayX extends AbstractPayment {
	public function purchase($request): JsonResponse {
		$payment = $this->creatNewPayment(Auth::id(), $request->input('oid'), $request->input('amount'));

		$data = [
			'merchant_order_id' => $payment->trade_no,
			'price_amount'      => (float) $request->input('amount'),
			'price_currency'    => 'CNY',
			'pay_currency'      => $request->input('type') == 1? 'ALIPAY' : 'WECHAT',
			'title'             => '支付单号：'.$payment->trade_no,
			'description'       => parent::$systemConfig['subject_name']?: parent::$systemConfig['website_name'],
			'callback_url'      => (parent::$systemConfig['website_callback_url']?: parent::$systemConfig['website_url']).'/callback/notify?method=bitpayx',
			'success_url'       => parent::$systemConfig['website_url'].'/invoices',
			'cancel_url'        => parent::$systemConfig['website_url'],
			'token'             => $this->sign($this->prepareSignId($payment->trade_no)),
		];

		$result = json_decode($this->mprequest($data), true);

		if($result['status'] === 200 || $result['status'] === 201){
			$result['payment_url'] .= '&lang=zh';
			Payment::whereId($payment->id)->update(['url' => $result['payment_url']]);

			return Response::json([
				'status'  => 'success',
				'url'     => $result['payment_url'],
				'message' => '创建订单成功!'
			]);
		}

		return Response::json(['status' => 'fail', 'data' => $result, 'message' => '创建订单失败!']);
	}

	private function sign($data) {
		return strtolower(md5(md5($data).parent::$systemConfig['bitpay_secret']));
	}

	private function prepareSignId($tradeno) {
		$data_sign = [
			'merchant_order_id' => $tradeno,
			'secret'            => parent::$systemConfig['bitpay_secret'],
			'type'              => 'FIAT'
		];
		ksort($data_sign);

		return http_build_query($data_sign);
	}

	private function mprequest($data, $type = 'pay') {
		$client = new Client(['base_uri' => 'https://api.mugglepay.com/v1/', 'timeout' => 10]);

		if($type === 'query'){
			$request = $client->get('orders/merchant_order_id/status?id='.$data['merchant_order_id'],
				['json' => ['token' => parent::$systemConfig['bitpay_secret']]]);
		}else{// pay
			$request = $client->post('orders',
				['json' => ['token' => parent::$systemConfig['bitpay_secret']], 'body' => json_encode($data)]);
		}
		if($request->getStatusCode() != 200){
			Log::debug('BitPayX请求支付错误：'.var_export($request, true));
		}

		return $request->getBody();
	}

	public function notify($request): void {
		$inputString = file_get_contents('php://input', 'r');
		$inputStripped = str_replace(["\r", "\n", "\t", "\v"], '', $inputString);
		$inputJSON = json_decode($inputStripped, true); //convert JSON into array
		$data = [];
		if($inputJSON != null){
			$data = [
				'status'            => $inputJSON['status'],
				'order_id'          => $inputJSON['order_id'],
				'merchant_order_id' => $inputJSON['merchant_order_id'],
				'price_amount'      => $inputJSON['price_amount'],
				'price_currency'    => $inputJSON['price_currency'],
				'created_at_t'      => $inputJSON['created_at_t'],
			];
		}
		// 准备待签名数据
		$str_to_sign = $this->prepareSignId($inputJSON['merchant_order_id']);
		$isPaid = $data != null && $data['status'] != null && $data['status'] === 'PAID';

		if($this->sign($str_to_sign) === $inputJSON['token'] && $isPaid){
			$this->postPayment($inputJSON['merchant_order_id'], 'BitPayX');
			echo json_encode(['status' => 200]);
		}else{
			echo json_encode(['status' => 400]);
		}
		exit();
	}
}
