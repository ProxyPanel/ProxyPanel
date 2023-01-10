<?php

namespace App\Payments;

use App\Components\CurrencyExchange;
use App\Models\Payment;
use App\Payments\Library\Gateway;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Srmklive\PayPal\Services\ExpressCheckout;

class PayPal extends Gateway
{
    protected $provider;

    public function __construct()
    {
        $this->provider = new ExpressCheckout();
        $config = [
            'mode' => 'live',
            'live' => [
                'username'    => sysConfig('paypal_username'),
                'password'    => sysConfig('paypal_password'),
                'secret'      => sysConfig('paypal_secret'),
                'certificate' => sysConfig('paypal_certificate'),
                'app_id'      => sysConfig('paypal_app_id'),
            ],

            'payment_action' => 'Sale',
            'currency'       => 'USD',
            'billing_type'   => 'MerchantInitiatedBilling',
            'notify_url'     => route('payment.notify', ['method' => 'paypal']),
            'locale'         => 'zh_CN',
            'validate_ssl'   => true,
        ];
        $this->provider->setApiCredentials($config);
    }

    public function purchase($request): JsonResponse
    {
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $data = $this->getCheckoutData($payment->trade_no, $payment->amount);

        try {
            $response = $this->provider->setExpressCheckout($data);
            if (! $response['paypal_link']) {
                Log::error('【Paypal】处理错误：'.var_export($response, true));

                return Response::json(['status' => 'fail', 'message' => '创建订单失败，请使用其他方式或通知管理员！']);
            }
            $payment->update(['url' => $response['paypal_link']]);

            return Response::json(['status' => 'success', 'url' => $response['paypal_link'], 'message' => '创建订单成功!']);
        } catch (Exception $e) {
            $payment->failed();
            Log::error('【PayPal】错误: '.$e->getMessage());
            exit;
        }
    }

    public function getCheckout(Request $request)
    {
        $token = $request->get('token');
        $PayerID = $request->get('PayerID');

        // Verify Express Checkout Token
        $response = $this->provider->getExpressCheckoutDetails($token);

        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            $payment = Payment::whereTradeNo($response['INVNUM'])->firstOrFail();
            $data = $this->getCheckoutData($payment->trade_no, $payment->amount);
            // Perform transaction on PayPal
            $payment_status = $this->provider->doExpressCheckoutPayment($data, $token, $PayerID);
            $status = $payment_status['PAYMENTINFO_0_PAYMENTSTATUS'];

            if (! strcasecmp($status, 'Completed') || ! strcasecmp($status, 'Processed')) {
                Log::notice("【Paypal】Order $payment->order_id has been paid successfully!");
                $payment->order->paid();
            } else {
                Log::alert("【PayPal】Error processing PayPal payment for Order $payment->id!");
            }
        }

        return redirect(route('invoice'));
    }

    public function notify($request): void
    {
        $request->merge(['cmd' => '_notify-validate']);
        foreach ($request->input() as $key => $value) {
            if ($value === null) {
                $request->request->set($key, '');
            }
        }
        $post = $request->all();

        $response = (string) $this->provider->verifyIPN($post);

        if ($response === 'VERIFIED' && $request['invoice']) {
            if ($this->paymentReceived($request['invoice'])) {
                exit('success');
            }
        } else {
            Log::error('【Paypal】交易失败');
        }
        exit('fail');
    }

    protected function getCheckoutData($trade_no, $amount): array
    {
        $amount = 0.3 + CurrencyExchange::convert('USD', $amount);

        return [
            'invoice_id'          => $trade_no,
            'items'               => [
                [
                    'name'  => sysConfig('subject_name') ?: sysConfig('website_name'),
                    'price' => $amount,
                    'desc'  => 'Description for'.(sysConfig('subject_name') ?: sysConfig('website_name')),
                    'qty'   => 1,
                ],
            ],
            'invoice_description' => $trade_no,
            'return_url'          => route('paypal.checkout'),
            'cancel_url'          => route('invoice'),
            'total'               => $amount,
        ];
    }
}
