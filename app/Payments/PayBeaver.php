<?php

/**
 * Created by PayBeaver <merchant.paybeaver.com>
 * Version: 2020-12-06.
 */

namespace App\Payments;

use App\Payments\Library\Gateway;
use Auth;
use Http;
use Illuminate\Http\JsonResponse;
use Log;
use Response;

class PayBeaver extends Gateway
{
    private $appId;
    private $appSecret;
    private $url = 'https://api.paybeaver.com/api/v1/developer';

    public function __construct()
    {
        $this->appId = sysConfig('paybeaver_app_id');
        $this->appSecret = sysConfig('paybeaver_app_secret');
    }

    public function purchase($request): JsonResponse
    {
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $result = $this->createOrder([
            'app_id'            => $this->appId,
            'merchant_order_id' => $payment->trade_no,
            'price_amount'      => $payment->amount * 100,
            'notify_url'        => route('payment.notify', ['method' => 'paybeaver']),
            'return_url'        => route('invoice'),
        ]);

        if (isset($result['message'])) {
            Log::alert('【海狸支付】创建订单错误：'.$result['message']);

            return Response::json(['status' => 'fail', 'message' => '创建订单失败：'.$result['message']]);
        }

        if (! isset($result['data']['pay_url'])) {
            Log::alert('【海狸支付】创建订单错误：未获取到支付链接'.var_export($result, true));

            return Response::json(['status' => 'fail', 'message' => '创建订单失败：未知错误']);
        }

        $payment->update(['url' => $result['data']['pay_url']]);

        return Response::json(['status' => 'success', 'url' => $result['data']['pay_url'], 'message' => '创建订单成功!']);
    }

    private function createOrder($params)
    {
        $params['sign'] = $this->sign($params);

        $response = Http::post($this->url.'/orders', $params);

        if ($response->ok()) {
            return $response->json();
        }

        Log::alert('【海狸支付】创建订单失败：'.var_export($response->json(), true));

        return ['status' => 'fail', 'message' => '获取失败！请检查配置信息'];
    }

    private function sign($params)
    {
        if (isset($params['sign'])) {
            unset($params['sign']);
        }
        ksort($params, SORT_STRING);

        return strtolower(md5(http_build_query($params).$this->appSecret));
    }

    public function notify($request): void
    {
        if (! $this->paybeaverVerify($request->post())) {
            exit(json_encode(['status' => 400]));
        }

        if ($request->has(['merchant_order_id']) && $this->paymentReceived($request->input(['merchant_order_id']))) {
            exit(json_encode(['status' => 200]));
        }

        Log::error('【海狸支付】交易失败：'.var_export($request->all(), true));

        exit(json_encode(['status' => 500]));
    }

    private function paybeaverVerify($params)
    {
        return hash_equals($params['sign'], $this->sign($params));
    }
}
