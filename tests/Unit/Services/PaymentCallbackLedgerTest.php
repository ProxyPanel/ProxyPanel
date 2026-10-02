<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Models\User;
use App\Models\UserCreditLog;
use App\Utils\Library\PaymentHelper;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * 回调留档：本地单号靠值域认、金额以分入库、凭证字段打码、超长报文截断。
 */
class PaymentCallbackLedgerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_records_a_callback_that_matches_nothing(): void
    {
        $log = PaymentHelper::createPaymentCallback($this->notify([
            'method' => 'epay',
            'trade_no' => 'PLATFORM-ONLY',
            'out_trade_no' => 'NOT-A-PAYMENT',
            'sign' => 'LEAK-ME',
        ]));

        $this->assertNull($log->trade_no);
        $this->assertSame('epay', $log->method);
        $this->assertSame('PLATFORM-ONLY', $log->out_trade_no);
        $this->assertSame(0, $log->getRawOriginal('amount'));
        $this->assertSame(0, (int) $log->status);
        $this->assertStringNotContainsString('LEAK-ME', $log->payload);
    }

    /** 本地单号只能拿候选值去 payment 表里对出来，键名在各网关含义相反. */
    public function test_links_the_callback_by_value_rather_than_by_key_name(): void
    {
        $payment = $this->payment('LOCAL-TRADE-NO');

        $log = PaymentHelper::createPaymentCallback($this->notify([
            'method' => 'epay',
            'trade_no' => 'PLATFORM-1',
            'out_trade_no' => $payment->trade_no,
        ]));

        $this->assertSame($payment->trade_no, $log->trade_no);
        $this->assertSame('PLATFORM-1', $log->out_trade_no);
        // payment.amount 读出来是元，入库要乘 100 变成分
        $this->assertSame(990, $log->getRawOriginal('amount'));

        // 反过来：支付宝把 trade_no 用作平台单号，out_trade_no 才是本地单，同样能对上
        $aliPay = PaymentHelper::createPaymentCallback($this->notify([
            'method' => 'alipay',
            'out_trade_no' => $payment->trade_no,
            'trade_no' => 'ALI-2026',
        ]));
        $this->assertSame($payment->trade_no, $aliPay->trade_no);
    }

    /** 对不上单号的回调也留原文；payload 是 TEXT，超限要截. */
    public function test_caps_an_oversized_payload(): void
    {
        $log = PaymentHelper::createPaymentCallback($this->notify([
            'method' => 'unknown',
            'blob' => str_repeat('B', 20000),
        ]));

        $this->assertNull($log->trade_no);
        $this->assertSame(0, $log->getRawOriginal('amount'));
        $this->assertLessThan(16000, mb_strlen($log->payload));
    }

    /** 没有入口行时按单号找回最近一条未履约留档. */
    public function test_marks_the_standing_ledger_row_when_there_is_no_callback_request(): void
    {
        $payment = $this->payment('MANUAL'.strtoupper(substr(uniqid(), -6)));
        $log = PaymentHelper::createPaymentCallback($this->notify([
            'method' => 'epay',
            'out_trade_no' => $payment->trade_no,
            'sign' => 'BAD-SIGN',
        ]));

        $this->assertSame(PaymentCallback::STATUS_UNFULFILLED, (int) $log->status);

        $this->assertTrue(PaymentHelper::paymentReceived($payment->trade_no));
        $this->assertSame(PaymentCallback::STATUS_FULFILLED, (int) $log->fresh()->status);
    }

    private function notify(array $payload): Request
    {
        $method = $payload['method'] ?? null;
        unset($payload['method']);

        return Request::create('/callback/notify?method='.($method ?? ''), 'POST', $payload);
    }

    /** 履约判据在订单行锁里：重复回调只确认，余额只加一次. */
    public function test_fulfills_a_credit_order_under_a_row_lock(): void
    {
        $payment = $this->payment('LOCK'.strtoupper(substr(uniqid(), -6)));

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->assertTrue(PaymentHelper::paymentReceived($payment->trade_no));
        $sql = implode(' ', array_column(DB::getQueryLog(), 'query'));
        DB::disableQueryLog();

        $this->assertStringContainsString('for update', $sql);

        $this->assertTrue(PaymentHelper::paymentReceived($payment->trade_no));
        $this->assertSame(1, UserCreditLog::whereUserId($payment->user_id)->count());
        $this->assertSame(9.9, (float) $payment->user->fresh()->credit);
    }

    private function payment(string $tradeNo): Payment
    {
        $user = User::create(['username' => 'ledger-test@example.com', 'passwd' => bcrypt('secret')]);
        $order = Order::create([
            'sn' => 'SN'.strtoupper($tradeNo),
            'user_id' => $user->id,
            'amount' => 9.9,
            'origin_amount' => 9.9,
        ]);

        return Payment::create(['trade_no' => $tradeNo, 'user_id' => $user->id, 'order_id' => $order->id, 'amount' => 9.9]);
    }
}
