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
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $data = $this->getCheckoutSessionData($payment->trade_no, $payment->amount);

        try {
            $session = Session::create($data);

            $url = route('stripe-checkout', ['session_id' => $session->id]);
            $payment->update(['url' => $url]);

            return Response::json(['status' => 'success', 'url' => $url, 'message' => '创建订单成功!']);
        } catch (Exception $e) {
            Log::error('【Stripe】错误: '.$e->getMessage());
            exit;
        }
    }

    protected function getCheckoutSessionData(string $tradeNo, int $amount): array
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
        return view('user.stripe-checkout', ['session_id' => $session_id]);
    }

    // url = '/callback/notify?method=stripe'
    public function notify($request): void
    {
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $endpointSecret = sysConfig('stripe_signing_secret');
        $event = null;
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

        Log::info('Passed signature verification!');
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
