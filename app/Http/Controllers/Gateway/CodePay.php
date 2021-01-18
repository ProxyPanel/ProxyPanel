<?php

namespace App\Http\Controllers\Gateway;

use Auth;
use Illuminate\Http\JsonResponse;
use Log;
use Response;

class CodePay extends AbstractPayment
{
    public function purchase($request): JsonResponse
    {
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $data = [
            'id'         => sysConfig('codepay_id'),
            'pay_id'     => $payment->trade_no,
            'type'       => $request->input('type'),            //1支付宝支付 2QQ钱包 3微信支付
            'price'      => $payment->amount,
            'page'       => 1,
            'outTime'    => 900,
            'notify_url' => route('payment.notify', ['method' => 'codepay']),
            'return_url' => route('invoice'),
        ];
        $data['sign'] = $this->aliStyleSign($data, sysConfig('codepay_key'));

        $url = sysConfig('codepay_url').http_build_query($data);
        $payment->update(['url' => $url]);

        return Response::json(['status' => 'success', 'url' => $url, 'message' => '创建订单成功!']);
    }

    public function notify($request): void
    {
        $tradeNo = $request->input('pay_id');
        if ($tradeNo && $request->input('pay_no')
            && $this->verify($request->except('method'), sysConfig('codepay_key'), $request->input('sign'), false)) {
            if ($this->paymentReceived($tradeNo)) {
                exit('success');
            }
        } else {
            Log::info('码支付：交易失败');
        }
        exit('fail');
    }
}
