<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Notifications\PaymentReceived;
use Str;

class PaymentService
{
    final public function createPayment(int $uid, int $oid, float|int $amount): Payment
    {
        $payment = new Payment();
        $payment->trade_no = Str::random(8);
        $payment->user_id = $uid;
        $payment->order_id = $oid;
        $payment->amount = $amount;
        $payment->save();

        return $payment;
    }

    /**
     * @param  string  $trade_no  本地订单号
     * @param  string  $out_trade_no  外部订单号
     * @param  float|int  $amount  交易金额
     */
    final protected function createPaymentCallback(string $trade_no, string $out_trade_no, float|int $amount): int
    {
        $log = new PaymentCallback();
        $log->trade_no = $trade_no;
        $log->out_trade_no = $out_trade_no;
        $log->amount = $amount;

        return $log->save();
    }

    protected function paymentReceived(string $tradeNo): bool
    {
        $payment = Payment::whereTradeNo($tradeNo)->with('order')->first();
        if ($payment) {
            $ret = $payment->order->complete();
            if ($ret) {
                $payment->user->notify(new PaymentReceived($payment->order->sn, $payment->amount_tag));
            }

            return $ret;
        }

        return false;
    }
}
