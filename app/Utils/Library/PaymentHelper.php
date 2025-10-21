<?php

namespace App\Utils\Library;

use App\Events\PaymentStatusUpdated;
use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Notifications\PaymentReceived;
use Str;

class PaymentHelper
{
    /**
     * MD5验签.
     *
     * @param  array  $data  未加密的数组信息
     * @param  string  $key  密钥
     * @param  string  $signature  加密的签名
     * @param  bool  $filter  是否清理空值
     */
    public static function verify(array $data, string $key, string $signature, bool $filter = true): bool
    {
        return hash_equals(self::aliStyleSign($data, $key, $filter), $signature);
    }

    /**
     *  Alipay式数据MD5签名.
     *
     * @param  array  $data  需要加密的数组
     * @param  string  $key  尾部的密钥
     * @param  bool  $filter  是否清理空值
     * @return string md5加密后的数据
     */
    public static function aliStyleSign(array $data, string $key, bool $filter = true): string
    { // 依据: https://opendocs.alipay.com/open/common/104741
        unset($data['sign'], $data['sign_type']); // 剃离sign, sign_type
        if ($filter) {
            $data = array_filter($data); // 剃离空值
        }

        ksort($data, SORT_STRING); // 排序

        return md5(urldecode(http_build_query($data)).$key); // 拼接
    }

    /**
     * @param  int  $uid  用户ID
     * @param  int  $oid  订单ID
     * @param  float|int  $amount  交易金额
     */
    public static function createPayment(int $uid, int $oid, float|int $amount): Payment
    {
        $payment = new Payment;
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
    public static function createPaymentCallback(string $trade_no, string $out_trade_no, float|int $amount): bool
    {
        $log = new PaymentCallback;
        $log->trade_no = $trade_no;
        $log->out_trade_no = $out_trade_no;
        $log->amount = $amount;

        return $log->save();
    }

    /**
     * @param  string  $tradeNo  本地订单号
     */
    public static function paymentReceived(string $tradeNo): bool
    {
        $payment = Payment::whereTradeNo($tradeNo)->with('order')->first();
        if ($payment) {
            $ret = $payment->order->complete();
            if ($ret) {
                $payment->user->notify(new PaymentReceived($payment->order->sn, $payment->amount_tag));
                broadcast(new PaymentStatusUpdated($tradeNo, 'success', trans('common.success_item', ['attribute' => trans('user.pay')]))); // 触发支付状态更新事件
            }

            return $ret;
        }

        return false;
    }
}
