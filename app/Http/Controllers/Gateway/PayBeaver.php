<?php

/**
 * Created by PayBeaver <merchant.paybeaver.com>
 * Version: 2020-12-06
 */

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Log;
use Auth;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class PayBeaver extends AbstractPayment
{
    private $appId;
    private $appSecret;
    private $url = 'https://api.paybeaver.com/api/v1/developer';

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function purchase($request): JsonResponse
    {
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $result = $this->createOrder([
            'app_id' => $this->appId,
            'merchant_order_id' => $payment->trade_no,
            'price_amount' => $payment->amount * 100,
            'notify_url' => route('payment.notify', ['method' => 'paybeaver']),
            'return_url' => route('invoice'),
        ]);

        if (isset($result['message'])) {
            Log::warning('创建订单错误：'.$result['message']);
            return Response::json(['status' => 'fail', 'message' => '创建订单失败：'.$result['message']]);
        }

        if (!isset($result['data']) || !isset($result['data']['pay_url'])) {
            Log::warning('创建订单错误：未知错误');
            return Response::json(['status' => 'fail', 'message' => '创建订单失败：未知错误']);
        }

        $payment->update(['url' => $result['data']['pay_url']]);
        return Response::json(['status' => 'success', 'url' => $result['data']['pay_url'], 'message' => '创建订单成功!']);
    }

    public function notify($request): void
    {
        if (!$this->paybeaverVerify($request->post())) {
            exit(json_encode(['status' => 400]));
        }

        $tradeNo = $request->input(['merchant_order_id']);
        $payment = Payment::whereTradeNo($tradeNo)->first();
        if ($payment) {
            $ret = $payment->order->update(['status' => 2]);
            if ($ret) {
                exit(json_encode(['status' => 200]));
            }
        }

        exit(json_encode(['status' => 500]));
    }

    protected function createOrder($params)
    {
        $params['sign'] = $this->sign($params);
        return $this->request('/orders', $params);
    }

    protected function paybeaverVerify($params)
    {
        // Log::warning('got sign ' . $params['sign']);
        // Log::warning('calc sign ' . $this->sign($params));
        return hash_equals($params['sign'], $this->sign($params));
    }

    protected function sign($params)
    {
        // Log::warning('paybeaver app secret: ' . $this->appSecret);
        // Log::warning('query: ' . http_build_query($params) . $this->appSecret);
        if (isset($params['sign'])) unset($params['sign']);
        ksort($params);
        reset($params);
        return strtolower(md5(http_build_query($params) . $this->appSecret));
    }

    protected function request($path, $data) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "{$this->url}{$path}");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);
        curl_close($curl);
        return json_decode($data, true);
    }
}
