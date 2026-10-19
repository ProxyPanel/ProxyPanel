<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Models\User;
use App\Models\UserCreditLog;
use App\Notifications\Custom;
use App\Utils\Library\PaymentHelper;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * 回调留档：本地单号靠值域认、金额以分入库、凭证字段打码、超长报文截断。
 */
class PaymentCallbackLedgerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_records_a_callback_that_matches_nothing(): void
    {
        Notification::fake();

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
        Notification::fake();

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

    /** 对不上号的回调要提醒管理员，但探测流量不能把提醒刷成洪水. */
    public function test_unmatched_callback_alerts_the_admin_only_once_per_window(): void
    {
        Notification::fake();

        // 提醒收件人固定是 id=1 的管理员，空库上先造出来（DatabaseTransactions 会回滚）
        $admin = $this->admin();

        foreach (['PROBE-A', 'PROBE-B'] as $no) {
            PaymentHelper::createPaymentCallback($this->notify(['method' => 'epay', 'out_trade_no' => $no]));
        }

        Notification::assertSentToTimes($admin, Custom::class, 1);

        // 窗口过后要能再提醒，否则去重会变成永久静默
        $this->travel(11)->minutes();
        PaymentHelper::createPaymentCallback($this->notify(['method' => 'epay', 'out_trade_no' => 'PROBE-C']));
        Notification::assertSentToTimes($admin, Custom::class, 2);
    }

    public function test_matched_callback_does_not_alert(): void
    {
        Notification::fake();
        $payment = $this->payment('MATCHED'.strtoupper(substr(uniqid(), -6)));

        PaymentHelper::createPaymentCallback($this->notify([
            'method' => 'epay',
            'out_trade_no' => $payment->trade_no,
        ]));

        Notification::assertNothingSent();
    }

    /** manual 的确认/拒绝链接不带单号，只有 payment.id 的短码；认得出就不是孤儿回调. */
    public function test_a_manual_callback_is_attributed_by_its_signed_payment_id(): void
    {
        Notification::fake();
        $payment = $this->payment('MANUALSIGN'.strtoupper(substr(uniqid(), -6)));

        $log = PaymentHelper::createPaymentCallback($this->notify([
            'method' => 'manual',
            'sign' => PaymentHelper::encodePaymentSign($payment->id),
            'status' => '1',
        ]));

        $this->assertSame($payment->trade_no, $log->trade_no);
        $this->assertSame(990, $log->getRawOriginal('amount'));

        Notification::assertNothingSent();
    }

    /** 解不开的短码没有单号可对：仍按无人推进处理. */
    public function test_an_unsolvable_manual_sign_still_alerts(): void
    {
        Notification::fake();
        $admin = $this->admin();
        cache()->forget('payment_callback:unmatched_alert');

        $log = PaymentHelper::createPaymentCallback($this->notify([
            'method' => 'manual',
            'sign' => 'not-a-valid-code',
            'status' => '1',
        ]));

        $this->assertNull($log->trade_no);

        Notification::assertSentToTimes($admin, Custom::class, 1);
    }

    /** 码支付的本地单号在 pay_id，键名含义只有按值域对才认得出. */
    public function test_a_codepay_callback_is_attributed_by_pay_id(): void
    {
        Notification::fake();
        $payment = $this->payment('CODEPAYID'.strtoupper(substr(uniqid(), -6)));

        $log = PaymentHelper::createPaymentCallback($this->notify([
            'method' => 'codepay',
            'pay_id' => $payment->trade_no,
            'pay_no' => 'PLATFORM-9',
        ]));

        $this->assertSame($payment->trade_no, $log->trade_no);

        Notification::assertNothingSent();
    }

    /** 关闭单仍可被迟到付款复活并交付：这是 2021 年起的原始语义，改它要当成行为变更. */
    public function test_a_closed_order_is_still_fulfilled_by_a_late_callback(): void
    {
        $payment = $this->payment('LATE'.strtoupper(substr(uniqid(), -6)));
        $this->assertTrue($payment->order->close());
        $this->assertSame(-1, (int) Order::find($payment->order_id)->status);

        $this->assertTrue(PaymentHelper::paymentReceived($payment->trade_no));

        $this->assertSame(2, (int) Order::find($payment->order_id)->status);
        $this->assertSame(9.9, (float) $payment->user->fresh()->credit);
        $this->assertSame(1, UserCreditLog::whereUserId($payment->user_id)->count());
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
