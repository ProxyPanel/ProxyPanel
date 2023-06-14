<?php

namespace App\Utils\Payments;

use App\Services\PaymentService;
use App\Utils\Library\Templates\Gateway;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Xhat\Payjs\Payjs as Pay;

class PayJs extends PaymentService implements Gateway
{
    private static array $config;

    public function __construct()
    {
        self::$config = [
            'mchid' => sysConfig('payjs_mch_id'),   // 配置商户号
            'key' => sysConfig('payjs_key'),   // 配置通信密钥
        ];
    }

    public function purchase(Request $request): JsonResponse
    {
        $payment = $this->createPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $result = (new Pay($this::$config))->cashier([
            'body' => sysConfig('subject_name') ?: sysConfig('website_name'),
            'total_fee' => $payment->amount * 100,
            'out_trade_no' => $payment->trade_no,
            'notify_url' => route('payment.notify', ['method' => 'payjs']),
        ]);

        // 获取收款二维码内容
        $payment->update(['qr_code' => 1, 'url' => $result]);

        //$this->addPamentCallback($payment->trade_no, null, $payment->amount * 100);
        return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
    }

    public function notify(Request $request): void
    {
        $data = (new Pay($this::$config))->notify();

        if ($data['return_code'] == 1) {
            if ($this->paymentReceived($data['out_trade_no'])) {
                exit('success');
            }
        } else {
            Log::error('【PayJs】交易失败：'.var_export($data, true));
        }
        exit('fail');
    }
}
