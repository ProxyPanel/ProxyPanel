<?php


namespace App\Http\Controllers\Gateway;


use App\Http\Models\Payment;
use Auth;
use Illuminate\Http\Request;
use Response;

class BitpayX extends AbstractPayment
{
	private $bitpayGatewayUri= 'https://api.mugglepay.com/v1/';

	public function mprequest($data, $type = 'pay')
	{
		$headers = ['content-type: application/json', 'token: '.parent::$systemConfig['bitpay_secret']];
		$curl = curl_init();
		if($type === 'pay'){
			$this->bitpayGatewayUri .= 'orders';
			curl_setopt($curl, CURLOPT_URL, $this->bitpayGatewayUri);
			curl_setopt($curl, CURLOPT_POST, 1);
			$data_string = json_encode($data);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
		}elseif($type === 'query'){
			$this->bitpayGatewayUri .= 'orders/merchant_order_id/status?id='.$data['merchant_order_id'];
			curl_setopt($curl, CURLOPT_URL, $this->bitpayGatewayUri);
			curl_setopt($curl, CURLOPT_HTTPGET, 1);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		$data = curl_exec($curl);
		curl_close($curl);

		return $data;
	}

	/**
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function purchase(Request $request)
	{
		$payment = new Payment();
		$payment->sn = self::generateGuid();
		$payment->user_id = Auth::user()->id;
		$payment->oid = $request->input('oid');
		$payment->amount = $request->input('amount');
		$payment->save();

		$data = [
			'merchant_order_id' => $payment->sn,
			'price_amount'      => (float)$request->input('amount'),
			'price_currency'    => 'CNY',
			'pay_currency'      => $request->input('type') == 1? 'ALIPAY' : 'WECHAT',
			'title'             => '支付单号：'.$payment->sn,
			'description'       => parent::$systemConfig['subject_name']? : parent::$systemConfig['website_name'],
			'callback_url'      => (parent::$systemConfig['website_callback_url']? : parent::$systemConfig['website_url']).'/payment/notify',
			'success_url'       => parent::$systemConfig['website_url'].'/invoices',
			'cancel_url'        => parent::$systemConfig['website_url'],
			'token'             => $this->sign($this->prepareSignId($payment->sn)),

		];

		$result = json_decode($this->mprequest($data), TRUE);


		if($result['status'] === 200 || $result['status'] === 201){
			$result['payment_url'] .= '&lang=zh';

			return Response::json(['status' => 'success', 'url' => $result['payment_url'] .= '&lang=zh', 'message' => '创建订单成功!']);
		}

		return Response::json(['status' => 'fail', 'data' => $result, 'message' => '创建订单失败!']);
	}

	public function sign($data)
	{
		return strtolower(md5(md5($data).parent::$systemConfig['bitpay_secret']));
	}

	public function prepareSignId($tradeno)
	{
		$data_sign = [
			'merchant_order_id' => $tradeno,
			'secret'            => parent::$systemConfig['bitpay_secret'],
			'type'              => 'FIAT',
		];
		ksort($data_sign);

		return http_build_query($data_sign);
	}

	public function notify(Request $request)
	{
		$inputString = file_get_contents('php://input', 'r');
		$inputStripped = str_replace(["\r", "\n", "\t", "\v"], '', $inputString);
		$inputJSON = json_decode($inputStripped, TRUE); //convert JSON into array
		$data = [];
		if($inputJSON !== NULL){
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
		$resultVerify = $this->verify($str_to_sign, $inputJSON['token']);
		$isPaid = $data !== NULL && $data['status'] !== NULL && $data['status'] === 'PAID';

		if($resultVerify && $isPaid){
			$this->postPayment($inputJSON['merchant_order_id'], 'BitPayX');
			$return = [];
			$return['status'] = 200;
			echo json_encode($return);
		}else{
			$return = [];
			$return['status'] = 400;
			echo json_encode($return);
		}
		exit();
	}

	public function verify($data, $signature)
	{
		$mySign = $this->sign($data);

		return $mySign === $signature;
	}

	public function getReturnHTML(Request $request)
	{
		// TODO: Implement getReturnHTML() method.
	}

	public function getPurchaseHTML()
	{
		// TODO: Implement getPurchaseHTML() method.
	}
}