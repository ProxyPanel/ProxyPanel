<?php

namespace App\Utils\Payments;

use App\Models\Payment;
use App\Utils\Library\AlipayF2F;
use App\Utils\Library\PaymentHelper;
use App\Utils\Library\Templates\Gateway;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class F2FPay implements Gateway
{
    private static AlipayF2F $aliClient;

    public function __construct()
    {
        self::$aliClient = new AlipayF2F([
            'app_id' => sysConfig('f2fpay_app_id'),
            'ali_public_key' => sysConfig('f2fpay_public_key'),
            'rsa_private_key' => sysConfig('f2fpay_private_key'),
            'notify_url' => route('payment.notify', ['method' => 'f2fpay']),
        ]);
    }

    public static function metadata(): array
    {
        return [
            'key' => 'f2fpay',
            'method' => ['ali'],
            'settings' => [
                'f2fpay_app_id' => null,
                'f2fpay_private_key' => null,
                'f2fpay_public_key' => null,
            ],
        ];
    }

    public function purchase(Request $request): JsonResponse
    {
        $payment = PaymentHelper::createPayment(auth()->id(), $request->input('id'), $request->input('amount'));

        $data = [
            'subject' => sysConfig('subject_name') ?: sysConfig('website_name'),
            'out_trade_no' => $payment->trade_no,
            'total_amount' => $payment->amount,
        ];

        try {
            $result = self::$aliClient->qrCharge($data);
            $payment->update(['qr_code' => 1, 'url' => $result['qr_code']]);
        } catch (Exception $e) {
            Log::alert('【支付宝当面付】支付错误: '.$e->getMessage());
            $payment->failed();
            exit;
        }

        return response()->json(['status' => 'success', 'data' => $payment->trade_no, 'message' => trans('user.payment.order_creation.success')]);
    }

    public function notify(Request $request): void
    {
        try {
            if (sysConfig('f2fpay_app_id') === $request->input('app_id') && self::$aliClient->validate_notification_sign($request->except('method'), $request->input('sign'))) {
                $payment = Payment::whereTradeNo($request->input('out_trade_no'))->with('order')->first();
                if ($payment && abs($payment->amount - $request->input('total_amount')) < 0.01 && in_array($request->input('trade_status'), ['TRADE_FINISHED', 'TRADE_SUCCESS']) && PaymentHelper::paymentReceived($request->input('out_trade_no'))) {
                    exit('success');
                }
            }
            Log::error('【支付宝当面付】异步验证失败，尝试订单查询');
            if ($this->capture($request->input('out_trade_no'), $request->input('trade_no'))) {
                exit('success');
            }

            Log::notice('【支付宝当面付】异步验证失败：'.var_export($request->all(), true));
        } catch (Exception $e) {
            Log::alert('【支付宝当面付】回调信息错误: '.$e->getMessage());
            exit;
        }

        // 返回验证结果
        exit('fail');
    }

    public function capture(?string $trade_no = null, ?string $ali_trade_no = null): bool
    {
        $result = self::$aliClient->tradeQuery(array_filter([
            'out_trade_no' => $trade_no,
            'trade_no' => $ali_trade_no,
        ]));

        if ($result['code'] === '10000' && $result['msg'] === 'Success') {
            if ($result['out_trade_no'] && in_array($result['trade_status'], ['TRADE_FINISHED', 'TRADE_SUCCESS'])) {
                if (PaymentHelper::paymentReceived($result['out_trade_no'])) {
                    return true;
                }
                Log::error('【支付宝当面付】收单交易订单结算失败：'.var_export($result, true));

                return false;
            }
        } else {
            Log::error('【支付宝当面付】收单交易查询失败：'.var_export($result, true));
        }

        return false;
    }
}
