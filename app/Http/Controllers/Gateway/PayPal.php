<?php


namespace App\Http\Controllers\Gateway;

use App\Models\Order;
use App\Models\Payment;
use Auth;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Srmklive\PayPal\Services\ExpressCheckout;

class PayPal extends AbstractPayment {
	protected $provider;
	protected $exChange;

	public function __construct() {
		parent::__construct();
		$this->provider = new ExpressCheckout();
		$config = [
			'mode' => 'live',
			'live' => [
				'username'    => self::$systemConfig['paypal_username'],
				'password'    => self::$systemConfig['paypal_password'],
				'secret'      => self::$systemConfig['paypal_secret'],
				'certificate' => self::$systemConfig['paypal_certificate'],
				'app_id'      => self::$systemConfig['paypal_app_id'],
			],

			'payment_action' => 'Sale',
			'currency'       => 'USD',
			'billing_type'   => 'MerchantInitiatedBilling',
			'notify_url'     => (self::$systemConfig['website_callback_url']?: self::$systemConfig['website_url']).'/callback/notify?method=paypal',
			'locale'         => 'zh_CN',
			'validate_ssl'   => true,
		];
		$this->provider->setApiCredentials($config);
		$this->exChange = 7;
		$client = new Client(['timeout' => 5]);
		$exChangeRate = json_decode($client->get('http://api.k780.com/?app=finance.rate&scur=USD&tcur=CNY&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4')
		                                   ->getBody(), true);

		if($exChangeRate && $exChangeRate['success']){
			$this->exChange = $exChangeRate['result']['rate'];
		}
	}

	public function purchase($request): JsonResponse {
		$payment = $this->creatNewPayment(Auth::id(), $request->input('oid'), $request->input('amount'));

		$data = $this->getCheckoutData($payment->trade_no, $payment->amount);

		try{
			$response = $this->provider->setExpressCheckout($data);
			if(!$response['paypal_link']){
				Log::error(var_export($response, true));

				return Response::json(['status' => 'fail', 'message' => '创建订单失败，请使用其他方式或通知管理员！']);
			}
			Payment::whereId($payment->id)->update(['url' => $response['paypal_link']]);

			return Response::json(['status' => 'success', 'url' => $response['paypal_link'], 'message' => '创建订单成功!']);
		}catch(Exception $e){
			Log::error("【PayPal】错误: ".$e->getMessage());
			exit;
		}
	}

	protected function getCheckoutData($trade_no, $amount): array {
		$amount = 0.3 + ceil($amount / $this->exChange * 100) / 100;

		return [
			'invoice_id'          => $trade_no,
			'items'               => [
				[
					'name'  => self::$systemConfig['subject_name']?: self::$systemConfig['website_name'],
					'price' => $amount,
					'desc'  => 'Description for'.(self::$systemConfig['subject_name']?: self::$systemConfig['website_name']),
					'qty'   => 1
				]
			],
			'invoice_description' => $trade_no,
			'return_url'          => self::$systemConfig['website_url'].'/callback/checkout',
			'cancel_url'          => self::$systemConfig['website_url'].'/invoices',
			'total'               => $amount,
		];
	}

	public function getCheckout(Request $request) {
		$token = $request->get('token');
		$PayerID = $request->get('PayerID');

		// Verify Express Checkout Token
		$response = $this->provider->getExpressCheckoutDetails($token);

		if(in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])){
			$payment = Payment::whereTradeNo($response['INVNUM'])->first();
			$data = $this->getCheckoutData($payment->trade_no, $payment->amount);
			// Perform transaction on PayPal
			$payment_status = $this->provider->doExpressCheckoutPayment($data, $token, $PayerID);
			$status = $payment_status['PAYMENTINFO_0_PAYMENTSTATUS'];

			if(!strcasecmp($status, 'Completed') || !strcasecmp($status, 'Processed')){
				Log::info("Order $payment->oid has been paid successfully!");
				Order::whereOid($payment->oid)->update(['status' => 1]);
			}else{
				Log::error("Error processing PayPal payment for Order $payment->id!");
			}
		}

		return redirect('/invoices');
	}

	public function notify($request): void {
		$request->merge(['cmd' => '_notify-validate']);
		foreach($request->input() as $key => $value){
			if($value == null){
				$request->request->set($key, '');
			}
		}
		$post = $request->all();

		$response = (string) $this->provider->verifyIPN($post);

		if($response === 'VERIFIED' && $request['invoice']){
			if(Payment::whereTradeNo($request['invoice'])->first()->status == 0){
				$this->postPayment($request['invoice'], 'PayPal');
			}
			exit("success");
		}
		exit("fail");
	}
}
