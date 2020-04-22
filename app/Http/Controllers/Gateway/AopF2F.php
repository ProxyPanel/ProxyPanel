<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Models\Payment;
use Auth;
use Exception;
use Log;
use Omnipay\Alipay\Responses\AopCompletePurchaseResponse;
use Omnipay\Alipay\Responses\AopTradePreCreateResponse;
use Omnipay\Omnipay;
use Response;

class AopF2F extends AbstractPayment
{
	public function purchase($request)
	{
		$payment = new Payment();
		$payment->sn = self::generateGuid();
		$payment->user_id = Auth::user()->id;
		$payment->oid = $request->input('oid');
		$payment->amount = $request->input('amount');
		$payment->save();

		$gateway = $this->createGateway();

		$request = $gateway->purchase();
		$request->setBizContent([
			'subject'      => parent::$systemConfig['subject_name']? : parent::$systemConfig['website_name'],
			'out_trade_no' => $payment->sn,
			'total_amount' => $payment->amount
		]);

		/** @var AopTradePreCreateResponse $response */
		$aliResponse = $request->send();

		$payment->qr_code = 'http://qr.topscan.com/api.php?text='.$aliResponse->getQrCode().'&bg=ffffff&fg=000000&pt=1c73bd&m=10&w=400&el=1&inpt=1eabfc&logo=https://t.alipayobjects.com/tfscom/T1Z5XfXdxmXXXXXXXX.png';//后备：https://cli.im/api/qrcode/code?text=".$aliResponse->getQrCode()."&mhid=5EfGCwztyckhMHcmI9ZcOKs
		$payment->save();

		return Response::json(['status' => 'success', 'data' => $payment->sn, 'message' => '创建订单成功!']);
	}

	private function createGateway()
	{
		$gateway = Omnipay::create('Alipay_AopF2F');
		$gateway->setSignType('RSA2'); //RSA/RSA2
		$gateway->setAppId(parent::$systemConfig['f2fpay_app_id']);
		$gateway->setPrivateKey(parent::$systemConfig['f2fpay_private_key']); // 可以是路径，也可以是密钥内容
		$gateway->setAlipayPublicKey(parent::$systemConfig['f2fpay_public_key']); // 可以是路径，也可以是密钥内容
		$notifyUrl = (parent::$systemConfig['website_callback_url']? : parent::$systemConfig['website_url']).'/payment/notify';
		$gateway->setNotifyUrl($notifyUrl);

		return $gateway;
	}

	public function notify($request)
	{
		$gateway = self::createGateway();
		$aliRequest = $gateway->completePurchase();
		$aliRequest->setParams($_POST);

		try{
			/** @var AopCompletePurchaseResponse $response */
			$aliResponse = $aliRequest->send();
			$pid = $aliResponse->data('out_trade_no');
			if($aliResponse->isPaid()){
				self::postPayment($pid, '支付宝当面付');
				exit('success');
			}
		}catch(Exception $e){
			Log::error('支付宝当面付 '.$e);
			exit('fail');
		}
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