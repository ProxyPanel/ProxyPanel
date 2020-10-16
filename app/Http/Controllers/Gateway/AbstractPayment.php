<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use App\Models\PaymentCallback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Str;

abstract class AbstractPayment
{
    abstract public function purchase(Request $request): JsonResponse;

    abstract public function notify(Request $request): void;

    protected function creatNewPayment($uid, $oid, $amount): Payment
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
     * @param string $trade_no     本地订单号
     * @param string $out_trade_no 外部订单号
     * @param int    $amount       交易金额
     *
     * @return int
     */
    protected function addPamentCallback(string $trade_no, string $out_trade_no, int $amount): int
    {
        $log = new PaymentCallback();
        $log->trade_no = $trade_no;
        $log->out_trade_no = $out_trade_no;
        $log->amount = $amount;

        return $log->save();
    }

    // MD5验签
    protected function verify($data, $key, $signature, $filter = true): bool
    {
        return hash_equals($this->aliStyleSign($data, $key, $filter), $signature);
    }

    /**
     *  Alipay式数据MD5签名.
     *
     * @param array  $data   需要加密的数组
     * @param string $key    尾部的密钥
     * @param bool   $filter 是否清理空值
     *
     * @return string md5加密后的数据
     */
    protected function aliStyleSign(array $data, string $key, $filter = true): string
    {
        // 剃离sign，sign_type，空值
        unset($data['sign'], $data['sign_type']);
        if ($filter) {
            $data = array_filter($data);
        }

        // 排序
        ksort($data, SORT_STRING);
        reset($data);

        return md5(urldecode(http_build_query($data)).$key);
    }
}
