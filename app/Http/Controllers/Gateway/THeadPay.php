<?php

namespace App\Http\Controllers\Gateway;

use Auth;
use Http;
use Illuminate\Http\JsonResponse;
use Log;
use Response;

class THeadPay extends AbstractPayment
{
    public function purchase($request): JsonResponse
    {
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $data = [
            'mchid'        => sysConfig('theadpay_mchid'),
            'out_trade_no' => $payment->trade_no,
            'total_fee'    => (string) ($payment->amount * 100),
            'notify_url'   => route('payment.notify', ['method' => 'theadpay']),
        ];
        $data['sign'] = $this->sign($data);

        $response = Http::post(sysConfig('theadpay_url').'/orders', $data);
        if ($response->ok()) {
            $result = $response->json();
            if ($result['status'] === 'success') {
                $payment->update(['qr_code' => 1, 'url' => $result['code_url']]);

                return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
            } else {
                Log::error('【平头哥支付】 返回错误信息：'.$result['message']);
            }
        }

        Log::error('【平头哥支付】 支付渠道建立订单出现问题');

        return Response::json(['status' => 'fail', 'message' => '创建在线订单失败，请工单通知管理员！']);
    }

    private function sign($params)
    {
        unset($params['sign']);
        ksort($params, SORT_STRING);
        reset($params);
        $params['key'] = sysConfig('theadpay_key');

        return strtoupper(md5(http_build_query($params)));
    }

    public function notify($request): void
    {
        if ($this->verify_notify($request->post())) {
            $tradeNo = $request->input('out_trade_no');
            if ($tradeNo) {
                if ($this->paymentReceived($tradeNo)) {
                    exit(200);
                }
            } else {
                Log::error('【平头哥支付】交易失败：订单信息-'.var_export($request->all(), true));
            }
        }

        exit('fail');
    }

    private function verify_notify($params)
    {
        return $params['sign'] === $this->sign($params);
    }
}
