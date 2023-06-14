<?php

namespace App\Utils\Payments;

use App\Services\PaymentService;
use App\Utils\Library\PaymentHelper;
use App\Utils\Library\Templates\Gateway;
use Auth;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class EPay extends PaymentService implements Gateway
{
    public function purchase(Request $request): JsonResponse
    {
        $payment = $this->createPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $data = [
            'pid' => sysConfig('epay_mch_id'),
            'type' => [1 => 'alipay', 2 => 'qqpay', 3 => 'wxpay'][$request->input('type')] ?? 'alipay',
            'notify_url' => route('payment.notify', ['method' => 'epay']),
            'return_url' => route('invoice'),
            'out_trade_no' => $payment->trade_no,
            'name' => sysConfig('subject_name') ?: sysConfig('website_name'),
            'money' => $payment->amount,
            'sign_type' => 'MD5',
        ];
        $data['sign'] = PaymentHelper::aliStyleSign($data, sysConfig('epay_key'));

        $url = sysConfig('epay_url').'submit.php?'.http_build_query($data);
        $payment->update(['url' => $url]);

        return Response::json(['status' => 'success', 'url' => $url, 'message' => '创建订单成功!']);
    }

    public function notify(Request $request): void
    {
        if ($request->input('trade_status') === 'TRADE_SUCCESS' && $request->has('out_trade_no')
            && PaymentHelper::verify($request->except('method'), sysConfig('epay_key'), $request->input('sign'))) {
            if ($this->paymentReceived($request->input('out_trade_no'))) {
                exit('SUCCESS');
            }

            Log::error('【易支付】验签失败：'.var_export($request->all(), true));
        } else {
            Log::error('【易支付】交易失败：'.var_export($request->all(), true));
        }

        exit('FAIL');
    }

    public function queryInfo(): JsonResponse
    {
        $response = Http::get(sysConfig('epay_url').'api.php', [
            'act' => 'query',
            'pid' => sysConfig('epay_mch_id'),
            'key' => sysConfig('epay_key'),
        ]);

        if ($response->ok()) {
            return Response::json(['status' => 'success', 'data' => $response->json()]);
        }

        return Response::json(['status' => 'fail', 'message' => '获取失败！请检查配置信息']);
    }
}
