<?php

namespace App\Payments;

use App\Payments\Library\AlipayF2F;
use App\Payments\Library\Gateway;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Log;
use Response;

class F2Fpay extends Gateway
{
    private static $aliConfig;

    public function __construct()
    {
        self::$aliConfig = [
            'app_id'          => sysConfig('f2fpay_app_id'),
            'ali_public_key'  => sysConfig('f2fpay_public_key'),
            'rsa_private_key' => sysConfig('f2fpay_private_key'),
            'notify_url'      => route('payment.notify', ['method' => 'f2fpay']),
        ];
    }

    public function purchase($request): JsonResponse
    {
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        $data = [
            'subject'      => sysConfig('subject_name') ?: sysConfig('website_name'),
            'out_trade_no' => $payment->trade_no,
            'total_amount' => $payment->amount,
        ];

        try {
            $gateWay = new AlipayF2F(self::$aliConfig);
            $result = $gateWay->qrCharge($data);
            $payment->update(['qr_code' => 1, 'url' => $result['qr_code']]);
        } catch (Exception $e) {
            $payment->failed();
            Log::alert('【支付宝当面付】支付错误: '.$e->getMessage());
            exit;
        }

        return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
    }

    public function notify($request): void
    {
        try {
            $result = (new AlipayF2F(self::$aliConfig))->tradeQuery($request->only('out_trade_no', 'trade_no'));
            Log::notice('【支付宝当面付】回调验证查询：'.var_export($result, true));
        } catch (Exception $e) {
            Log::alert('【支付宝当面付】回调信息错误: '.$e->getMessage());
            exit;
        }

        if ($result['code'] === '10000' && $result['msg'] === 'Success') {
            if ($request->has('out_trade_no') && in_array($request->input('trade_status'), ['TRADE_FINISHED', 'TRADE_SUCCESS'])) {
                if ($this->paymentReceived($request->input('out_trade_no'))) {
                    exit('success');
                }
            } else {
                Log::error('【支付宝当面付】交易失败：'.var_export($request->all(), true));
            }
        } else {
            Log::error('【支付宝当面付】验证失败：'.var_export($result, true));
        }

        // 返回验证结果
        exit('fail');
    }
}
