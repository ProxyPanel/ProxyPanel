<?php

namespace App\Utils\Payments;

use App\Services\PaymentService;
use App\Utils\CurrencyExchange;
use App\Utils\Library\Templates\Gateway;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class PayPal extends PaymentService implements Gateway
{
    protected static \Srmklive\PayPal\Services\PayPal $provider;

    public function __construct()
    {
        self::$provider = \PayPal::setProvider();
        $config = [
            'mode' => 'live',
            'live' => [
                'client_id' => sysConfig('paypal_client_id'),
                'client_secret' => sysConfig('paypal_client_secret'),
                'app_id' => sysConfig('paypal_app_id'),
            ],
            'payment_action' => 'Sale',
            'currency' => 'USD',
            'notify_url' => route('payment.notify', ['method' => 'paypal']),
            'locale' => app()->getLocale(),
            'validate_ssl' => true,
        ];

        $allowedCurrencies = ['AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'INR', 'JPY', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'TWD', 'THB', 'USD', 'RUB', 'CNY'];

        self::$provider->setApiCredentials($config);
        self::$provider->getAccessToken();
    }

    public function purchase($request): JsonResponse
    {
        $payment = $this->createPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $data = $this->getCheckoutData($payment->trade_no, $payment->amount);

        $response = self::$provider->createOrder([
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => route('payment.notify', ['method' => 'paypal']),
                'cancel_url' => route('invoice'),
            ],
            'purchase_units' => [
                0 => [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '100.00',
                    ],
                ],
            ],
        ]);
        if (isset($response['id']) && $response['id'] != null) {
            Log::error('【Paypal】处理错误：'.var_export($response, true));

            return Response::json(['status' => 'fail', 'message' => '创建订单失败，请使用其他方式或通知管理员！']);
        }
        $payment->update(['url' => $response['paypal_link']]);

        foreach ($response['links'] as $links) {
            if ($links['rel'] === 'approve') {
                return Response::json(['status' => 'success', 'url' => $links['href'], 'message' => '创建订单成功!']);
            }
        }

        $payment->failed();
        Log::error('【PayPal】错误: ');
        exit;
    }

    protected function getCheckoutData(string $trade_no, float|int $amount): array
    {
        $converted = CurrencyExchange::convert('USD', $amount);
        if ($converted === false) {
            $converted = $amount / 7;
        }
        $amount = 0.3 + $converted;

        return [
            'intent' => 'CAPTURE',
            'invoice_id' => $trade_no,
            'items' => [
                [
                    'name' => sysConfig('subject_name') ?: sysConfig('website_name'),
                    'price' => $amount,
                    'desc' => 'Description for'.(sysConfig('subject_name') ?: sysConfig('website_name')),
                    'qty' => 1,
                ],
            ],
            'invoice_description' => $trade_no,
            'return_url' => route('payment.notify', ['method' => 'paypal']),
            'cancel_url' => route('invoice'),
            'total' => $amount,
        ];
    }

    public function notify(Request $request): void
    {
        $response = self::$provider->capturePaymentOrder($request['token']);

        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            if ($this->paymentReceived($request['invoice'])) {
                exit('success');
            }
        } else {
            Log::error('【Paypal】交易失败');
        }

        exit('fail');
    }
}
