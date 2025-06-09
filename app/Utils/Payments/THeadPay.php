<?php

namespace App\Utils\Payments;

use App\Utils\Library\PaymentHelper;
use App\Utils\Library\Templates\Gateway;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class THeadPay implements Gateway
{
    public static function metadata(): array
    {
        return [
            'key' => 'theadpay',
            'method' => ['ali'],
            'settings' => [
                'theadpay_url' => [
                    'type' => 'url',
                ],
                'theadpay_mchid' => null,
                'theadpay_key' => null,
            ],
        ];
    }

    public function purchase(Request $request): JsonResponse
    {
        $payment = PaymentHelper::createPayment(auth()->id(), $request->input('id'), $request->input('amount'));

        $data = [
            'mchid' => sysConfig('theadpay_mchid'),
            'out_trade_no' => $payment->trade_no,
            'total_fee' => (string) ($payment->amount * 100),
            'notify_url' => route('payment.notify', ['method' => 'theadpay']),
        ];
        $data['sign'] = $this->sign($data);

        $response = Http::post(sysConfig('theadpay_url').'/orders', $data);
        if ($response->ok()) {
            $result = $response->json();
            if ($result['status'] === 'success') {
                $payment->update(['qr_code' => 1, 'url' => $result['code_url']]);

                return response()->json(['status' => 'success', 'data' => $payment->trade_no, 'message' => trans('user.payment.order_creation.success')]);
            }
            $payment->failed();
            Log::error('【平头哥支付】 返回错误信息：'.$result['message']);
        }

        Log::alert('【平头哥支付】 支付渠道建立订单出现问题!');

        return response()->json(['status' => 'fail', 'message' => trans('user.payment.order_creation.failed')]);
    }

    private function sign(array $params): string
    {
        unset($params['sign']);
        ksort($params, SORT_STRING);
        $params['key'] = sysConfig('theadpay_key');

        return strtoupper(md5(http_build_query($params)));
    }

    public function notify(Request $request): void
    {
        if ($this->verify_notify($request->post())) {
            $tradeNo = $request->input('out_trade_no');
            if ($tradeNo) {
                if (PaymentHelper::paymentReceived($tradeNo)) {
                    exit(200);
                }
            } else {
                Log::error('【平头哥支付】交易失败：订单信息-'.var_export($request->all(), true));
            }
        }

        exit('fail');
    }

    private function verify_notify(array $params): bool
    {
        return $params['sign'] === $this->sign($params);
    }
}
