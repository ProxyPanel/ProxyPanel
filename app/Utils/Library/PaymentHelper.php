<?php

namespace App\Utils\Library;

use App\Events\PaymentStatusUpdated;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Notifications\PaymentReceived;
use App\Utils\IP;
use DB;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Log;
use Str;
use Throwable;

class PaymentHelper
{
    // 命中即打码：签名、密钥与卡凭证不进日志
    private const SENSITIVE_FIELD = '/(sign|token|secret|key|pass(word|wd)?|hash|authorization|card|cvv|cvc|e-?mail|phone|mobile|(buyer|payer)_id|logon_id|openid|unionid)/i';

    // 支付单号的发号尝试次数
    private const TRADE_NO_ATTEMPTS = 3;

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
     * 留档/日志用的支付报文：凭证类字段打码，超长值截断。
     */
    public static function loggable(?array $data): string
    {
        return var_export($data === null ? null : self::mask($data), true);
    }

    private static function mask(array $data): array
    {
        foreach ($data as $key => $value) {
            if (preg_match(self::SENSITIVE_FIELD, (string) $key)) {
                $value = '******';
            } elseif (is_array($value)) {
                $value = self::mask($value);
            } elseif (is_string($value) && strlen($value) > 64) {
                $value = substr($value, 0, 64).'...';
            }
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * 支付单号用 ULID：26 位纯大写字母数字、时间有序，且要在 insert 之前就拿到好去拼网关下单地址。
     * 唯一索引冲突时换号重试。
     *
     * @param  int  $uid  用户ID
     * @param  int  $oid  订单ID
     * @param  float|int  $amount  交易金额
     */
    public static function createPayment(int $uid, int $oid, float|int $amount): Payment
    {
        $last = null;

        for ($try = 0; $try < self::TRADE_NO_ATTEMPTS; $try++) {
            $payment = new Payment;
            $payment->trade_no = (string) Str::ulid();
            $payment->user_id = $uid;
            $payment->order_id = $oid;
            $payment->amount = $amount;

            try {
                $payment->save();

                return $payment;
            } catch (UniqueConstraintViolationException $e) {
                $last = $e;
                Log::warning('【支付单号】唯一索引冲突，换号重试：'.$payment->trade_no);
            }
        }

        throw $last;
    }

    /**
     * 记录刚进来的回调（此时还没验签），行 id 挂到 request 上供 paymentReceived() 回写。
     */
    public static function createPaymentCallback(Request $request): PaymentCallback
    {
        $payload = $request->all();
        $tradeNo = self::matchLocalTradeNo($payload);
        $payment = $tradeNo ? Payment::whereTradeNo($tradeNo)->first() : null;

        $log = PaymentCallback::create([
            'method' => Str::limit((string) ($request->query('method') ?: $request->input('method')), 32, ''),
            'trade_no' => $tradeNo,
            'out_trade_no' => self::matchPlatformTradeNo($payload, $tradeNo),
            'amount' => $payment?->amount ?? 0,
            // 按字符截到 15000：utf8mb4 最坏 4 字节一字符，TEXT 装得下
            'payload' => Str::limit(self::loggable($payload), 15000),
            'ip' => IP::getClientIp(),
            'status' => PaymentCallback::STATUS_UNFULFILLED,
        ]);

        $request->attributes->set('payment_callback_id', $log->id);

        return $log;
    }

    /**
     * 回调报文中「我们这边的支付单号」可能出现的字段名，按可信度排序。
     */
    private const LOCAL_ORDER_FIELDS = ['out_trade_no', 'merchant_order_id', 'merchant_order_no', 'order_id', 'invoice', 'client_reference_id', 'trade_no'];

    /**
     * 支付平台交易号可能出现的字段名，按可信度排序。
     */
    private const PLATFORM_ORDER_FIELDS = ['trade_no', 'transaction_id', 'txn_id', 'tradeId', 'payjsorderid', 'alipay_trade_no', 'order_no', 'payment_intent'];

    /**
     * 键名在不同网关含义相反，只有拿候选值查支付单才能认出本地单号。
     */
    private static function matchLocalTradeNo(array $payload): ?string
    {
        $candidates = [];
        foreach (self::LOCAL_ORDER_FIELDS as $field) {
            $value = $payload[$field] ?? null;
            if (is_string($value) && $value !== '') {
                $candidates[] = $value;
            }
        }

        if ($candidates === []) {
            return null;
        }

        $known = Payment::whereIn('trade_no', $candidates)->pluck('trade_no')->all();
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $known, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function matchPlatformTradeNo(array $payload, ?string $localTradeNo): ?string
    {
        foreach (self::PLATFORM_ORDER_FIELDS as $field) {
            $value = $payload[$field] ?? null;
            if (is_string($value) && $value !== '' && $value !== $localTradeNo) {
                return Str::limit($value, 64, '');
            }
        }

        return null;
    }

    /**
     * 履约成功后回写留档行；没有入口行时按单号找回最近一条未履约的。
     */
    private static function markPaymentCallbackFulfilled(string $tradeNo, Payment $payment): void
    {
        // 人工确认不走回调，没有入口行可挂，按单号找回
        $log = PaymentCallback::find(request()?->attributes->get('payment_callback_id'))
            ?? PaymentCallback::whereTradeNo($tradeNo)->where('status', PaymentCallback::STATUS_UNFULFILLED)->latest('id')->first();

        if (! $log) {
            return;
        }

        $log->update([
            'trade_no' => $log->trade_no ?: $tradeNo,
            'amount' => $log->amount ?: $payment->amount,
            'status' => PaymentCallback::STATUS_FULFILLED,
        ]);
    }

    /**
     * @param  string  $tradeNo  本地订单号
     */
    public static function paymentReceived(string $tradeNo): bool
    {
        $payment = Payment::whereTradeNo($tradeNo)->with('order')->first();
        if ($payment) {
            // 行锁内重取订单，履约只给先到的那条（2 是已支付）
            $ret = DB::transaction(function () use ($payment): bool {
                $order = Order::whereKey($payment->order_id)->lockForUpdate()->first();

                return (int) $order->status === 2 || $order->complete();
            });

            if ($ret) {
                // 回写排在通知与广播之前
                self::markPaymentCallbackFulfilled($tradeNo, $payment);

                // 通知与广播失败不能让已收款的回调收到 500
                try {
                    $payment->user->notify(new PaymentReceived($payment->order->sn, $payment->amount_tag));
                    broadcast(new PaymentStatusUpdated($tradeNo, 'success', trans('common.success_item', ['attribute' => trans('user.pay')]))); // 触发支付状态更新事件
                } catch (Throwable $e) {
                    Log::error('【支付履约】通知/广播失败：'.$e->getMessage());
                }
            }

            return $ret;
        }

        return false;
    }
}
