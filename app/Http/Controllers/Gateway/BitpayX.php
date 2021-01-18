<?php

namespace App\Http\Controllers\Gateway;

use Auth;
use Http;
use Illuminate\Http\JsonResponse;
use Log;
use Response;

class BitpayX extends AbstractPayment
{
    public function purchase($request): JsonResponse
    {
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $data = [
            'merchant_order_id' => $payment->trade_no,
            'price_amount'      => $payment->amount,
            'price_currency'    => 'CNY',
            'title'             => '支付单号：'.$payment->trade_no,
            'description'       => sysConfig('subject_name') ?: sysConfig('website_name'),
            'callback_url'      => route('payment.notify', ['method' => 'bitpayx']),
            'success_url'       => route('invoice'),
            'cancel_url'        => route('invoice'),
            'token'             => $this->sign($payment->trade_no),
        ];
        $result = $this->sendRequest($data);

        if ($result['status'] === 200 || $result['status'] === 201) {
            $result['payment_url'] .= '&lang=zh';
            $payment->update(['url' => $result['payment_url']]);

            return Response::json(['status' => 'success', 'url' => $result['payment_url'], 'message' => '创建订单成功!']);
        }

        Log::warning('创建订单错误：'.var_export($result, true));

        return Response::json(['status' => 'fail', 'message' => '创建订单失败!'.$result['error']]);
    }

    private function sign($tradeNo): string
    {
        $data = [
            'merchant_order_id' => $tradeNo,
            'secret'            => sysConfig('bitpay_secret'),
            'type'              => 'FIAT',
        ];

        return $this->aliStyleSign($data, sysConfig('bitpay_secret'));
    }

    private function sendRequest($data, $type = 'createOrder')
    {
        $client = Http::baseUrl('https://api.mugglepay.com/v1/')
            ->timeout(15)
            ->withHeaders([
                'token'        => sysConfig('bitpay_secret'),
                'content-type' => 'application/json',
            ]);

        if ($type === 'query') {
            $response = $client->get('orders/merchant_order_id/status?id='.$data['merchant_order_id']);
        } else {// Create Order
            $response = $client->post('orders', ['body' => json_encode($data)]);
        }
        if ($response->failed()) {
            Log::error('BitPayX请求支付错误：'.var_export($response, true));
        }

        return $response->json();
    }

    //Todo: Postman虚拟测试通过，需要真实数据参考验证
    public function notify($request): void
    {
        $tradeNo = $request->input(['merchant_order_id']);
        if ($request->input(['status']) === 'PAID' && hash_equals($this->sign($tradeNo), $request->input(['token']))) {
            if ($this->paymentReceived($tradeNo)) {
                exit(json_encode(['status' => 200]));
            }
        } else {
            Log::info('BitpayX：交易失败');
        }
        exit(json_encode(['status' => 400]));
    }
}
