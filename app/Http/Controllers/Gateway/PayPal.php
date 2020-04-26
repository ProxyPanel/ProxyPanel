<?php


namespace App\Http\Controllers\Gateway;

use App\Http\Models\Payment;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Log;
use Response;
use Srmklive\PayPal\Services\ExpressCheckout;

class PayPal extends AbstractPayment
{
	protected $provider;

	public function __construct()
	{
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
			'currency'       => env('PAYPAL_CURRENCY', 'USD'),
			'billing_type'   => 'MerchantInitiatedBilling',
			'notify_url'     => (self::$systemConfig['website_callback_url']? : self::$systemConfig['website_url']).'/callback/notify?method=paypal',
			'locale'         => 'zh-CN',
			'validate_ssl'   => TRUE,
		];
		$this->provider->setApiCredentials($config);
	}

	public function purchase(Request $request)
	{
		$payment = new Payment();
		$payment->sn = self::generateGuid();
		$payment->user_id = Auth::user()->id;
		$payment->oid = $request->input('oid');
		$payment->amount = $request->input('amount');
		$payment->save();

		$data = $this->getCheckoutData($payment->sn, $payment->amount);

		try{
			$response = $this->provider->setExpressCheckout($data);

			return Response::json(['status' => 'success', 'url' => $response['paypal_link'], 'message' => '创建订单成功!']);
		}catch(Exception $e){
			Log::error("【PayPal】错误: ".$e->getMessage());
			exit;
		}
	}

	protected function getCheckoutData($sn, $amount)
	{
		return [
			'invoice_id'          => $sn,
			'items'               => [
				[
					'name'  => self::$systemConfig['subject_name']? : self::$systemConfig['website_name'],
					'price' => $amount,
					'desc'  => 'Description for'.(self::$systemConfig['subject_name']? : self::$systemConfig['website_name']),
					'qty'   => 1
				]
			],
			'invoice_description' => $sn,
			'return_url'          => self::$systemConfig['website_url'].'/callback/checkout',
			'cancel_url'          => self::$systemConfig['website_url'].'/invoices',
			'total'               => $amount,
		];
	}

	public function getCheckout(Request $request)
	{
		$token = $request->get('token');
		$PayerID = $request->get('PayerID');

		// Verify Express Checkout Token
		$response = $this->provider->getExpressCheckoutDetails($token);

		if(in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])){
			$payment = Payment::whereSn($response['INVNUM'])->first();
			$data = $this->getCheckoutData($payment->sn, $payment->amount);
			// Perform transaction on PayPal
			$payment_status = $this->provider->doExpressCheckoutPayment($data, $token, $PayerID);
			$status = $payment_status['PAYMENTINFO_0_PAYMENTSTATUS'];

			if(!strcasecmp($status, 'Completed') || !strcasecmp($status, 'Processed')){
				Log::info("Order $payment->id has been paid successfully!");
			}else{
				Log::error("Error processing PayPal payment for Order $payment->id!");
			}
		}

		return redirect('/invoices');
	}

	public function notify(Request $request)
	{
		$request->merge(['cmd' => '_notify-validate']);
		$post = $request->all();

		$response = (string)$this->provider->verifyIPN($post);

		if($response === 'VERIFIED' && $request['mp_desc']){
			if(Payment::whereSn($request['mp_desc'])->first()->status == 0){
				self::postPayment($request['mp_desc'], 'PayPal');
			}
			exit("success");
		}
		exit("fail");
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