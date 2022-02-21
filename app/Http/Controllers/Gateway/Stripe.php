<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Log;
use Response;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Source;
use Stripe\Webhook;
use UnexpectedValueException;

class Stripe extends AbstractPayment
{
    public function __construct()
    {
        \Stripe\Stripe::setApiKey(sysConfig('stripe_secret_key'));
    }

    public function purchase($request): JsonResponse
    {
        $type = $request->input('type');
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        if ($type == 1 || $type == 3){
            $stripe_currency = sysConfig('stripe_currency');
            $ch = curl_init();
            $url = 'https://api.exchangerate-api.com/v4/latest/' . strtoupper($stripe_currency);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $currency = json_decode(curl_exec($ch));
            curl_close($ch);
            $price_exchanged = bcdiv((double)$payment->amount, $currency->rates->CNY, 10);
            $source = Source::create([
                'amount' => floor($price_exchanged * 100),
                'currency' => $stripe_currency,
                'type' => $type == 1 ? "alipay" : "wechat",
                'statement_descriptor' => $payment->trade_no,
                'metadata' => [
                    'user_id' => $payment->user_id,
                    'out_trade_no' => $payment->trade_no,
                    'identifier' => ''
                ],
                'redirect' => [
                    'return_url' => route('invoice')
                ]
            ]);
            if ($type == 3) {
                if (!$source['wechat']['qr_code_url']) {
                    Log::warning('创建订单错误：未知错误');
                    $payment->delete();

                    return response()->json(['code' => 0, 'msg' => '创建订单失败：未知错误']);
                }
                $payment->update(['qr_code' => 1, 'url' => $source['wechat']['qr_code_url']]);

                return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
            } else {
                if (!$source['redirect']['url']) {
                    Log::warning('创建订单错误：未知错误');
                    $payment->delete();

                    return response()->json(['code' => 0, 'msg' => '创建订单失败：未知错误']);
                }
                $payment->update(['url' => $source['redirect']['url']]);

                return Response::json(['status' => 'success', 'url' => $source['redirect']['url'], 'message' => '创建订单成功!']);
            }
        } else {
            $data = $this->getCheckoutSessionData($payment->trade_no, $payment->amount, $type);

            try {
                $session = Session::create($data);

                $url = route('stripe.checkout', ['session_id' => $session->id]);
                $payment->update(['url' => $url]);

                return Response::json(['status' => 'success', 'url' => $url, 'message' => '创建订单成功!']);
            } catch (Exception $e) {
                Log::error('【Stripe】错误: '.$e->getMessage());
                exit;
            }
        }
    }

    protected function getCheckoutSessionData(string $tradeNo, int $amount, int $type): array
    {
        $unitAmount = $amount * 100;

        return [
            'payment_method_types' => ['card'],
            'line_items'           => [
                [
                    'price_data' => [
                        'currency'     => 'usd',
                        'product_data' => ['name' => sysConfig('subject_name') ?: sysConfig('website_name')],
                        'unit_amount'  => $unitAmount,
                    ],
                    'quantity'   => 1,
                ],
            ],
            'mode'                 => 'payment',
            'success_url'          => route('invoice'),
            'cancel_url'           => route('invoice'),
            'client_reference_id'  => $tradeNo,
            'customer_email'       => Auth::getUser()->email,
        ];
    }

    // redirect to Stripe Payment url
    public function redirectPage($session_id)
    {
        return view('user.components.payment.stripe', ['session_id' => $session_id]);
    }

    // url = '/callback/notify?method=stripe'
    public function notify($request): void
    {
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $endpointSecret = sysConfig('stripe_signing_secret');
        $payload = @file_get_contents('php://input');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        Log::info('【Stripe】Passed signature verification!');
        switch ($event->type) {
            case 'checkout.session.completed':

                /* @var $session Session */
                $session = $event->data->object;

                // Check if the order is paid (e.g., from a card payment)
                //
                // A delayed notification payment will have an `unpaid` status, as
                // you're still waiting for funds to be transferred from the customer's
                // account.
                if ($session->payment_status == 'paid') {
                    // Fulfill the purchase
                    $this->paymentReceived($session->client_reference_id);
                }
                break;
            case 'checkout.session.async_payment_succeeded':
                $session = $event->data->object;
                // Fulfill the purchase
                $this->paymentReceived($session->client_reference_id);
                break;
            case 'checkout.session.async_payment_failed':
                $session = $event->data->object;
                // Send an email to the customer asking them to retry their order
                $this->failedPayment($session);
                break;
        }

        http_response_code(200);
        exit();
    }

    // 未支付成功则关闭订单
    public function failedPayment(Session $session)
    {
        $payment = Payment::whereTradeNo($session->client_reference_id)->first();
        if ($payment) {
            $payment->order->close();
        }
    }
}
