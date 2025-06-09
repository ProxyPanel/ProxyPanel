<?php

namespace App\Utils\Payments;

use App\Utils\Library\PaymentHelper;
use App\Utils\Library\Templates\Gateway;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class Cryptomus implements Gateway
{
    private const API_URL = 'https://api.cryptomus.com/';

    public static array $methodDetails = [
        'key' => 'cryptomus',
        'settings' => ['cryptomus_merchant_uuid', 'cryptomus_key'],
    ];

    private string $apiKey;

    private string $mid;

    public function __construct()
    {
        $this->mid = sysConfig('cryptomus_merchant_uuid');
        $this->apiKey = sysConfig('cryptomus_api_key');
    }

    public function purchase(Request $request): JsonResponse
    {
        $payment = PaymentHelper::createPayment(auth()->id(), $request->input('id'), $request->input('amount'));

        $result = $this->createOrder([
            'amount' => $payment->amount,
            'currency' => sysConfig('standard_currency'),
            'order_id' => $payment->trade_no,
            'lifetime' => 900,
            // 'network' => '',
            'is_payment_multiple' => false,
            'url_return' => route('shop.index'),
            'url_success' => route('invoice.index'),
            'url_callback' => route('payment.notify', ['method' => 'cryptomus']),
        ]);

        if (isset($result['state'], $result['result']['url']) && $result['state'] === 0) {
            $payment->update(['url' => $result['result']['url']]);

            return Response::json(['status' => 'success', 'url' => $result['result']['url'], 'message' => trans('user.payment.order_creation.success')]);
        }

        $payment->failed();
        if (isset($result['message'])) {
            Log::alert('【Cryptomus】创建订单错误：'.$result['message']);

            return Response::json(['status' => 'fail', 'message' => trans('user.payment.order_creation.failed')]);
        }

        if (! isset($result['result']['url'])) {
            Log::alert('【Cryptomus】创建订单错误：未获取到支付链接'.var_export($result, true));
        }

        return Response::json(['status' => 'fail', 'message' => trans('user.payment.order_creation.failed')]);
    }

    private function createOrder(array $params): array
    {
        $response = Http::withHeaders(['merchant' => $this->mid, 'sign' => $this->sign($params)])->asJson()->post(self::API_URL.'v1/payment', $params);

        if ($response->ok()) {
            return $response->json();
        }

        Log::alert('【Cryptomus】创建订单失败：'.var_export($response->json(), true));

        return ['status' => 'fail', 'message' => '获取失败！请检查配置信息'];
    }

    private function sign(array|string $data): string
    {
        if (isset($params['sign'])) {
            unset($params['sign']);
        }

        return md5(base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE)).$this->apiKey);
    }

    public function notify(Request $request): void
    {
        if (! $this->verify($request->post())) {
            exit(400);
        }

        if ($request->has(['is_final', 'status', 'order_id']) && in_array($request->input('status'), ['paid', 'paid_over'], true) && PaymentHelper::paymentReceived($request->input(['order_id']))) {
            exit(200);
        }

        Log::error('【Cryptomus】交易失败：'.var_export($request->all(), true));

        exit(500);
    }

    private function verify(array $params): bool
    {
        return hash_equals($params['sign'], $this->sign($params));
    }
}
