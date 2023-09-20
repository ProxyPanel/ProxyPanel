<?php

namespace App\Utils\Payments;

use App\Models\Payment;
use App\Services\PaymentService;
use App\Utils\Library\Templates\Gateway;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Source;
use Stripe\Webhook;
use UnexpectedValueException;

class Stripe extends PaymentService implements Gateway
{
    public function __construct()
    {
        \Stripe\Stripe::setApiKey(sysConfig('stripe_secret_key'));
    }

    public function purchase(Request $request): JsonResponse
    {
        $type = (int) $request->input('type');
        $payment = $this->createPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        if ($type === 1 || $type === 3) {
            $source = Source::create([
                'amount' => ceil($payment->amount * 100),
                'currency' => strtolower(sysConfig('standard_currency')),
                'type' => $type === 1 ? 'alipay' : 'wechat',
                'statement_descriptor' => $payment->trade_no,
                'metadata' => [
                    'user_id' => $payment->user_id,
                    'out_trade_no' => $payment->trade_no,
                    'identifier' => '',
                ],
                'redirect' => [
                    'return_url' => route('invoice'),
                ],
            ]);
            if ($type === 3) {
                if (! $source['wechat']['qr_code_url']) {
                    Log::warning('创建订单错误：未知错误');
                    $payment->failed();

                    return Response::json(['status' => 'fail', 'message' => '创建订单失败：未知错误']);
                }
                $payment->update(['qr_code' => 1, 'url' => $source['wechat']['qr_code_url']]);

                return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
            }

            if (! $source['redirect']['url']) {
                Log::warning('创建订单错误：未知错误');
                $payment->failed();

                return response()->json(['code' => 0, 'msg' => '创建订单失败：未知错误']);
            }
            $payment->update(['url' => $source['redirect']['url']]);

            return Response::json(['status' => 'success', 'url' => $source['redirect']['url'], 'message' => '创建订单成功!']);
        }

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

    protected function getCheckoutSessionData(string $tradeNo, float|int $amount, int $type): array
    {
        $unitAmount = $amount * 100;

        return [
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => sysConfig('subject_name') ?: sysConfig('website_name')],
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => route('invoice'),
            'cancel_url' => route('invoice'),
            'client_reference_id' => $tradeNo,
            'customer_email' => Auth::getUser()->email,
        ];
    }

    public function redirectPage($session_id)
    { // redirect to Stripe Payment url
        return view('user.components.payment.stripe', ['session_id' => $session_id]);
    }

    public function notify(Request $request): void
    { // url = '/callback/notify?method=stripe'
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $endpointSecret = sysConfig('stripe_signing_secret');
        $payload = @file_get_contents('php://input');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit;
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit;
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
                if ($session->payment_status === 'paid') {
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
        exit;
    }

    public function failedPayment(Session $session): void
    { // 未支付成功则关闭订单
        $payment = Payment::whereTradeNo($session->client_reference_id)->first();
        $payment?->order->close();
    }
}
