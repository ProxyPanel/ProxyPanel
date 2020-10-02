<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Auth;
use GuzzleHttp\Client;
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

        Log::error('创建订单错误：'.var_export($result, true));

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
        $client = new Client([
            'base_uri' => 'https://api.mugglepay.com/v1/',
            'timeout'  => 15,
            'headers'  => [
                'token'        => sysConfig('bitpay_secret'),
                'content-type' => 'application/json',
            ],
        ]);

        if ($type === 'query') {
            $request = $client->get('orders/merchant_order_id/status?id='.$data['merchant_order_id']);
        } else {// Create Order
            $request = $client->post('orders', ['body' => json_encode($data)]);
        }
        if ($request->getStatusCode() !== 200) {
            Log::error('BitPayX请求支付错误：'.var_export($request, true));
        }

        return json_decode($request->getBody(), true);
    }

    //Todo: Postman虚拟测试通过，需要真实数据参考验证
    public function notify($request): void
    {
        $tradeNo = $request->input(['merchant_order_id']);
        if ($request->input(['status']) === 'PAID' && hash_equals($this->sign($tradeNo), $request->input(['token']))) {
            $payment = Payment::whereTradeNo($tradeNo)->first();
            if ($payment) {
                $ret = $payment->order->update(['status' => 2]);
                if ($ret) {
                    exit(json_encode(['status' => 200]));
                }
            }
        }
        exit(json_encode(['status' => 400]));
    }
}
