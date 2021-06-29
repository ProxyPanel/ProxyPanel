<?php

namespace App\Http\Controllers\Gateway;

use Auth;
use Illuminate\Http\JsonResponse;
use Log;
use Response;

class BitpayX extends AbstractPayment
{
    public function purchase($request): JsonResponse
    {
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));
        $data = [
            'merchant_order_id'  => $payment->trade_no,
            'price_amount'       => (float) $payment->amount,
            'price_currency'     => 'CNY',
            'title'              => '支付单号：'.$payment->trade_no,
            'description'        => sysConfig('subject_name') ?: sysConfig('website_name'),
            'callback_url'       => route('payment.notify', ['method' => 'bitpayx']),
            'success_url'        => route('invoice'),
            'cancel_url'         => route('invoice'),
            'token'              => $this->sign($this->prepareSignId($payment->trade_no)),
        ];
        if ($request->input('type') == 1) {
            $data['pay_currency'] = 'ALIPAY';
        } elseif ($request->input('type') == 3) {
            $data['pay_currency'] = 'WECHAT';
        }
        $result = $this->sendRequest($data);
        if ($result['status'] === 200 || $result['status'] === 201) {
            $result['payment_url'] .= '&lang=zh';
            $payment->update(['url' => $result['payment_url']]);

            return Response::json(['status' => 'success', 'url' => $result['payment_url'], 'message' => '创建订单成功!']);
        }
        Log::warning('创建订单错误：'.var_export($result, true));

        return Response::json(['status' => 'fail', 'message' => '创建订单失败!'.$result['error']]);
    }

    private function prepareSignId($tradeNo): string
    {
        $data = [
            'merchant_order_id' => $tradeNo,
            'secret' => sysConfig('bitpay_secret'),
            'type' => 'FIAT',
        ];
        ksort($data);

        return http_build_query($data);
    }

    private function sign($data)
    {
        return strtolower(md5(md5($data).sysConfig('bitpay_secret')));
    }

    private function sendRequest($data, $type = 'createOrder')
    {
        $bitpayGatewayUri = 'https://api.mugglepay.com/v1/';
        $headers = ['content-type: application/json', 'token: '.sysConfig('bitpay_secret')];
        $curl = curl_init();
        if ($type === 'createOrder') {
            $bitpayGatewayUri .= 'orders';
            curl_setopt($curl, CURLOPT_URL, $bitpayGatewayUri);
            curl_setopt($curl, CURLOPT_POST, 1);
            $data_string = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        } elseif ($type === 'query') {
            $bitpayGatewayUri .= 'orders/merchant_order_id/status?id='.$data['merchant_order_id'];
            curl_setopt($curl, CURLOPT_URL, $bitpayGatewayUri);
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result, true);
    }

    private function verify_bit($data, $signature)
    {
        $mySign = $this->sign($data);

        return $mySign === $signature;
    }

    //Todo: Postman虚拟测试通过，需要真实数据参考验证
    public function notify($request): void
    {
        $tradeNo = $request->input(['merchant_order_id']);
        // 准备待签名数据
        $str_to_sign = $this->prepareSignId($tradeNo);
        if ($request->input(['status']) === 'PAID' && $this->verify_bit($str_to_sign, $request->input(['token']))) {
            if ($this->paymentReceived($tradeNo)) {
                exit(json_encode(['status' => 200]));
            }
        } else {
            Log::info('BitpayX：交易失败');
        }
        exit(json_encode(['status' => 400]));
    }
}
