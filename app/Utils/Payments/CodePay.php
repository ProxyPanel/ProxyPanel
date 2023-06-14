<?php

namespace App\Utils\Payments;

use App\Services\PaymentService;
use App\Utils\Library\PaymentHelper;
use App\Utils\Library\Templates\Gateway;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class CodePay extends PaymentService implements Gateway
{
    public function purchase(Request $request): JsonResponse
    {
        $payment = $this->createPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $data = [
            'id' => sysConfig('codepay_id'),
            'pay_id' => $payment->trade_no,
            'type' => $request->input('type'), //1支付宝支付 2QQ钱包 3微信支付
            'price' => $payment->amount,
            'page' => 1,
            'outTime' => 900,
            'notify_url' => route('payment.notify', ['method' => 'codepay']),
            'return_url' => route('invoice'),
        ];
        $data['sign'] = PaymentHelper::aliStyleSign($data, sysConfig('codepay_key'));

        $url = sysConfig('codepay_url').http_build_query($data);
        $payment->update(['url' => $url]);

        return Response::json(['status' => 'success', 'url' => $url, 'message' => '创建订单成功!']);
    }

    public function notify(Request $request): void
    {
        $tradeNo = $request->input('pay_id');
        if ($tradeNo && $request->input('pay_no')
            && PaymentHelper::verify($request->except('method'), sysConfig('codepay_key'), $request->input('sign'), false)) {
            if ($this->paymentReceived($tradeNo)) {
                exit('success');
            }

            Log::error('【码支付】验签失败：'.var_export($request->all(), true));
        } else {
            Log::error('【码支付】交易失败：'.var_export($request->all(), true));
        }
        exit('fail');
    }
}
