<?php

namespace Tests\Unit\Http;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Models\User;
use App\Models\UserCreditLog;
use App\Notifications\Custom;
use App\Notifications\PaymentReceived;
use App\Utils\Library\PaymentHelper;
use Hashids\Hashids;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Notification;
use Tests\TestCase;

/**
 * 各网关 notify() 的完整 HTTP 往返：收到→验签→履约→回文本。
 *
 * 覆盖易支付（EPay）、码支付（CodePay）、Stripe、Manual 四个网关；除 Manual 外都以 exit() 收尾，
 * 只能由 PaymentNotifyChildRunner 的 CLI 子进程代发。报文键名语义按网关取候选：
 * 易支付系本地单号在 out_trade_no、平台流水在 trade_no；码支付本地单号在 pay_id、平台号在 pay_no；
 * Manual 的单号由 sign（payment.id 短码）认领；Stripe 的本地单号嵌在 data.object.client_reference_id，顶层永远对不上，入口即触发未匹配提醒、履约后回补台账。
 * 未覆盖：PayPal/Cryptomus/PayJs/F2Fpay/PayBeaver/THeadPay（notify 前段依赖外部 HTTP 往返或 SDK 会话，
 * 验签算法本身由 PaymentHelperSignTest 覆盖）与 Credit（无外部回调）。
 */
class PaymentNotifyRoundTripTest extends TestCase
{
    use DatabaseTransactions;
    use PaymentNotifyChildRunner;

    private const EPAY_KEY = 'epay-roundtrip-key';

    private const CODEPAY_KEY = 'codepay-roundtrip-key';

    private const STRIPE_SECRET = 'whsec_roundtrip_test';

    private static bool $stalePurged = false;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        cache()->forget('payment_callback:unmatched_alert');
        config(['database.connections.cleanup' => config('database.connections.mysql')]);
        // 履约通知的渠道由后台配置决定，空值时 via() 为空、fake 不记录；这里钉成 mail 让断言确定
        config(['settings.payment_received_notification' => ['mail']]);

        if (! self::$stalePurged) {
            self::$stalePurged = true;
            $this->purgeStaleChildFixtures();
        }
    }

    protected function tearDown(): void
    {
        // 去重认领键留在进程级 array 缓存里，会让后续测试（如回调台账）的提醒断言拿不到首发
        cache()->forget('payment_callback:unmatched_alert');
        $this->purgeCommittedChildren();

        parent::tearDown();
    }

    public function test_epay_round_trip_replies_success_and_fulfills_once(): void
    {
        $report = $this->runNotifyChild($this->epaySpec(['commit' => true]));

        $this->assertSame('SUCCESS', $report['body']);
        $this->assertSame(2, $report['order_status']);
        $this->assertSame(990, $report['credit_raw']);
        $this->assertSame(1, $report['credit_logs']);
        $this->assertSame(0, $report['custom_alerts']);
        $this->assertSame(1, $report['fulfill_notices']);

        $callback = $report['callbacks'][0];
        $this->assertSame('epay', $callback['method']);
        $this->assertSame($report['trade_no'], $callback['trade_no']);
        $this->assertSame('PLATFORM-EPAY-'.$report['uniq'], $callback['out_trade_no']);
        $this->assertSame(990, $callback['amount']);
        $this->assertSame(PaymentCallback::STATUS_FULFILLED, $callback['status']);
        $this->assertTrue($callback['sign_masked']);
        $this->assertFalse($callback['sign_leaked']);

        // 相同报文重投：仍回 SUCCESS，但只履约一次，台账各留一行
        $replay = $this->runNotifyChild($this->epaySpec([
            'commit' => true,
            'uniq' => $report['uniq'],
            'fixture' => ['mode' => 'reuse', 'trade_no' => $report['trade_no']],
        ]));

        $this->assertSame('SUCCESS', $replay['body']);
        $this->assertSame(2, $replay['order_status']);
        $this->assertSame(990, $replay['credit_raw']);
        $this->assertSame(1, $replay['credit_logs']);
        $this->assertCount(1, $replay['callbacks']);
        $this->assertSame(PaymentCallback::STATUS_FULFILLED, $replay['callbacks'][0]['status']);
    }

    /** 错误签名：先落档再分发，台账留下但不履约. */
    public function test_epay_wrong_signature_keeps_ledger_but_does_not_fulfill(): void
    {
        $report = $this->runNotifyChild($this->epaySpec([
            'sign' => ['algo' => 'ali', 'key' => 'wrong-'.self::EPAY_KEY, 'filter' => true],
        ]));

        $this->assertSame('FAIL', $report['body']);
        $this->assertSame(0, $report['order_status']);
        $this->assertSame(0, $report['credit_raw']);
        $this->assertSame(0, $report['credit_logs']);
        $this->assertSame(0, $report['custom_alerts']);
        $this->assertSame(0, $report['fulfill_notices']);

        $callback = $report['callbacks'][0];
        $this->assertSame('epay', $callback['method']);
        $this->assertSame($report['trade_no'], $callback['trade_no']);
        $this->assertSame(990, $callback['amount']);
        $this->assertSame(PaymentCallback::STATUS_UNFULFILLED, $callback['status']);
        $this->assertTrue($callback['sign_masked']);
        $this->assertFalse($callback['sign_leaked']);
    }

    /** trade_no 对不上任何本地单：落档 + 提醒 id=1 管理员. */
    public function test_epay_unmatched_trade_no_records_ledger_and_alerts_admin(): void
    {
        $report = $this->runNotifyChild($this->epaySpec([
            'fixture' => ['mode' => 'none'],
            'payload' => [
                'pid' => '12345',
                'type' => 'alipay',
                'trade_no' => 'PLATFORM-GHOST-{uniq}',
                'out_trade_no' => 'GHOST-{uniq}',
                'name' => 'roundtrip',
                'money' => '9.9',
                'trade_status' => 'TRADE_SUCCESS',
                'sign_type' => 'MD5',
            ],
        ]));

        $this->assertSame('FAIL', $report['body']);
        $this->assertSame(1, $report['custom_alerts']);
        $this->assertSame(0, $report['fulfill_notices']);

        $callback = $report['callbacks'][0];
        $this->assertSame('epay', $callback['method']);
        $this->assertNull($callback['trade_no']);
        $this->assertSame('PLATFORM-GHOST-'.$report['uniq'], $callback['out_trade_no']);
        $this->assertSame(0, $callback['amount']);
        $this->assertSame(PaymentCallback::STATUS_UNFULFILLED, $callback['status']);
    }

    public function test_codepay_round_trip_replies_success_and_fulfills_once(): void
    {
        $report = $this->runNotifyChild($this->codepaySpec(['commit' => true]));

        $this->assertSame('success', $report['body']);
        $this->assertSame(2, $report['order_status']);
        $this->assertSame(990, $report['credit_raw']);
        $this->assertSame(1, $report['credit_logs']);
        $this->assertSame(1, $report['fulfill_notices']);
        $this->assertSame(0, $report['custom_alerts']);

        $callback = $report['callbacks'][0];
        $this->assertSame('codepay', $callback['method']);
        $this->assertSame($report['trade_no'], $callback['trade_no']);
        // 平台号在 pay_no，不在台账的候选字段里
        $this->assertNull($callback['out_trade_no']);
        $this->assertSame(990, $callback['amount']);
        $this->assertSame(PaymentCallback::STATUS_FULFILLED, $callback['status']);
        $this->assertTrue($callback['sign_masked']);
        $this->assertFalse($callback['sign_leaked']);

        $replay = $this->runNotifyChild($this->codepaySpec([
            'commit' => true,
            'uniq' => $report['uniq'],
            'fixture' => ['mode' => 'reuse', 'trade_no' => $report['trade_no']],
        ]));

        $this->assertSame('success', $replay['body']);
        $this->assertSame(990, $replay['credit_raw']);
        $this->assertSame(1, $replay['credit_logs']);
        $this->assertCount(1, $replay['callbacks']);
        $this->assertSame(PaymentCallback::STATUS_FULFILLED, $replay['callbacks'][0]['status']);
    }

    public function test_codepay_wrong_signature_keeps_ledger_but_does_not_fulfill(): void
    {
        $report = $this->runNotifyChild($this->codepaySpec([
            'sign' => ['algo' => 'ali', 'key' => 'wrong-'.self::CODEPAY_KEY, 'filter' => false],
        ]));

        $this->assertSame('fail', $report['body']);
        $this->assertSame(0, $report['order_status']);
        $this->assertSame(0, $report['credit_raw']);
        $this->assertSame(0, $report['fulfill_notices']);
        // 单号认得出就不再提醒：这是已验签失败、由网关自己记日志的一类
        $this->assertSame(0, $report['custom_alerts']);

        $callback = $report['callbacks'][0];
        $this->assertSame('codepay', $callback['method']);
        $this->assertSame($report['trade_no'], $callback['trade_no']);
        $this->assertSame(990, $callback['amount']);
        $this->assertSame(PaymentCallback::STATUS_UNFULFILLED, $callback['status']);
        $this->assertTrue($callback['sign_masked']);
        $this->assertFalse($callback['sign_leaked']);
    }

    public function test_stripe_round_trip_verifies_webhook_and_fulfills_once(): void
    {
        $report = $this->runNotifyChild($this->stripeSpec(['commit' => true]));

        $this->assertSame('', $report['body']);
        $this->assertSame(200, $report['http_status']);
        $this->assertSame(2, $report['order_status']);
        $this->assertSame(990, $report['credit_raw']);
        $this->assertSame(1, $report['credit_logs']);
        $this->assertSame(1, $report['fulfill_notices']);
        // Stripe 事件的本地单号嵌在 data.object.client_reference_id，顶层对不上：入口即提醒，履约后台账回补
        $this->assertSame(1, $report['custom_alerts']);

        $callback = $report['callbacks'][0];
        $this->assertSame('stripe', $callback['method']);
        $this->assertSame($report['trade_no'], $callback['trade_no']);
        $this->assertNull($callback['out_trade_no']);
        $this->assertSame(990, $callback['amount']);
        $this->assertSame(PaymentCallback::STATUS_FULFILLED, $callback['status']);

        $replay = $this->runNotifyChild($this->stripeSpec([
            'commit' => true,
            'uniq' => $report['uniq'],
            'fixture' => ['mode' => 'reuse', 'trade_no' => $report['trade_no']],
        ]));

        $this->assertSame('', $replay['body']);
        $this->assertSame(200, $replay['http_status']);
        $this->assertSame(990, $replay['credit_raw']);
        $this->assertSame(1, $replay['credit_logs']);
        $this->assertCount(1, $replay['callbacks']);
        $this->assertSame(PaymentCallback::STATUS_FULFILLED, $replay['callbacks'][0]['status']);
    }

    /** Stripe 验签失败回 400：台账仍先落档，订单不推进. */
    public function test_stripe_bad_signature_returns_400_and_keeps_ledger(): void
    {
        $report = $this->runNotifyChild($this->stripeSpec([
            'sign' => ['algo' => 'stripe', 'secret' => self::STRIPE_SECRET, 'tampered' => true],
        ]));

        $this->assertSame('', $report['body']);
        $this->assertSame(400, $report['http_status']);
        $this->assertSame(0, $report['order_status']);
        $this->assertSame(0, $report['credit_raw']);
        $this->assertSame(0, $report['credit_logs']);
        $this->assertSame(0, $report['fulfill_notices']);
        $this->assertSame(1, $report['custom_alerts']);

        $callback = $report['callbacks'][0];
        $this->assertSame('stripe', $callback['method']);
        $this->assertNull($callback['trade_no']);
        $this->assertSame(PaymentCallback::STATUS_UNFULFILLED, $callback['status']);
    }

    public function test_manual_round_trip_fulfills_pending_order(): void
    {
        $admin = $this->admin();
        $payment = $this->pendingManualPayment();
        $marker = (int) (PaymentCallback::max('id') ?? 0);
        $sign = (new Hashids(config('app.key'), 8))->encode($payment->id);

        $this->post(route('payment.notify', ['method' => 'manual']), ['sign' => $sign, 'status' => '1'])->assertOk();

        $this->assertSame(2, (int) $payment->order->fresh()->status);
        $this->assertSame(990, (int) $payment->user->fresh()->getRawOriginal('credit'));
        $this->assertSame(1, UserCreditLog::whereUserId($payment->user_id)->count());

        $row = PaymentCallback::where('id', '>', $marker)->sole();
        $this->assertSame('manual', $row->method);
        // 落档时就按 sign 认领到本地单：单号与金额不再等履约回补，管理员的确认动作不该触发未匹配提醒
        $this->assertSame($payment->trade_no, $row->trade_no);
        $this->assertSame(990, (int) $row->getRawOriginal('amount'));
        $this->assertSame(PaymentCallback::STATUS_FULFILLED, (int) $row->status);
        $this->assertStringContainsString("'sign' => '******'", $row->payload);
        $this->assertStringNotContainsString($sign, $row->payload);

        Notification::assertSentToTimes($payment->user, PaymentReceived::class, 1);
        Notification::assertSentToTimes($admin, Custom::class, 0);
    }

    public function test_manual_replay_does_not_fulfill_twice(): void
    {
        $payment = $this->pendingManualPayment();
        $marker = (int) (PaymentCallback::max('id') ?? 0);
        $sign = (new Hashids(config('app.key'), 8))->encode($payment->id);
        $uri = route('payment.notify', ['method' => 'manual']);

        $this->post($uri, ['sign' => $sign, 'status' => '1'])->assertOk();
        $this->post($uri, ['sign' => $sign, 'status' => '1'])->assertOk();

        $this->assertSame(2, (int) $payment->order->fresh()->status);
        $this->assertSame(990, (int) $payment->user->fresh()->getRawOriginal('credit'));
        $this->assertSame(1, UserCreditLog::whereUserId($payment->user_id)->count());

        $rows = PaymentCallback::where('id', '>', $marker)->orderBy('id')->get();
        $this->assertCount(2, $rows);
        $this->assertSame(PaymentCallback::STATUS_FULFILLED, (int) $rows[0]->status);
        // 重放时订单已是 2 而非 1，不再走履约，第二行台账停在入口状态
        $this->assertSame(PaymentCallback::STATUS_UNFULFILLED, (int) $rows[1]->status);
        $this->assertSame($payment->trade_no, $rows[1]->trade_no);
    }

    public function test_manual_bad_sign_renders_error_and_keeps_ledger(): void
    {
        $admin = $this->admin();
        $payment = $this->pendingManualPayment();
        $marker = (int) (PaymentCallback::max('id') ?? 0);

        $this->post(route('payment.notify', ['method' => 'manual']), ['sign' => 'not-a-valid-code', 'status' => '1'])
            ->assertOk()
            ->assertSee('No enough information');

        $this->assertSame(1, (int) $payment->order->fresh()->status);
        $this->assertSame(0, (int) $payment->user->fresh()->getRawOriginal('credit'));
        $this->assertSame(0, UserCreditLog::whereUserId($payment->user_id)->count());

        $row = PaymentCallback::where('id', '>', $marker)->sole();
        $this->assertNull($row->trade_no);
        $this->assertSame(PaymentCallback::STATUS_UNFULFILLED, (int) $row->status);

        // 认领短码解不开时，未匹配提醒照旧：认领判据不是把提醒关掉
        Notification::assertSentToTimes($admin, Custom::class, 1);
    }

    /** 未知 method 的探测流量：404 之前先落档，未匹配提醒 10 分钟窗口内只发一次. */
    public function test_unmatched_callbacks_alert_admin_once_per_window(): void
    {
        $admin = $this->admin();
        $marker = (int) (PaymentCallback::max('id') ?? 0);
        $uri = route('payment.notify', ['method' => 'unknown']);

        foreach (['GHOST-A', 'GHOST-B'] as $ghost) {
            $this->post($uri, ['out_trade_no' => $ghost])->assertNotFound();
        }

        Notification::assertSentToTimes($admin, Custom::class, 1);

        $rows = PaymentCallback::where('id', '>', $marker)->orderBy('id')->get();
        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertSame('unknown', $row->method);
            $this->assertNull($row->trade_no);
            $this->assertSame(PaymentCallback::STATUS_UNFULFILLED, (int) $row->status);
        }

        // 窗口过后要能再提醒，否则去重会变成永久静默
        $this->travel(11)->minutes();
        $this->post($uri, ['out_trade_no' => 'GHOST-C'])->assertNotFound();
        Notification::assertSentToTimes($admin, Custom::class, 2);
    }

    private function epaySpec(array $overrides = []): array
    {
        return array_merge([
            'gateway' => 'epay',
            'config' => ['epay_key' => self::EPAY_KEY, 'payment_received_notification' => ['mail']],
            'fixture' => ['mode' => 'create', 'amount' => 9.9],
            // 易支付系：本地单号在 out_trade_no，trade_no 是平台流水
            'payload' => [
                'pid' => '12345',
                'type' => 'alipay',
                'trade_no' => 'PLATFORM-EPAY-{uniq}',
                'out_trade_no' => '{trade_no}',
                'name' => 'roundtrip',
                'money' => '9.9',
                'trade_status' => 'TRADE_SUCCESS',
                'sign_type' => 'MD5',
            ],
            'sign' => ['algo' => 'ali', 'key' => self::EPAY_KEY, 'filter' => true],
        ], $overrides);
    }

    private function codepaySpec(array $overrides = []): array
    {
        return array_merge([
            'gateway' => 'codepay',
            'config' => ['codepay_key' => self::CODEPAY_KEY, 'payment_received_notification' => ['mail']],
            'fixture' => ['mode' => 'create', 'amount' => 9.9],
            // 码支付：本地单号在 pay_id，pay_no 才是平台号；验签不清空值（filter=false）。
            // param 填 '0' 不留空串：空串会被 ConvertEmptyStringsToNull 转 NULL，而 http_build_query 跳过 NULL，两侧签名串对不上
            'payload' => [
                'id' => '1001',
                'pay_id' => '{trade_no}',
                'pay_no' => 'CODEPAY-{uniq}',
                'money' => '9.90',
                'price' => '9.90',
                'param' => '0',
                'type' => '1',
            ],
            'sign' => ['algo' => 'ali', 'key' => self::CODEPAY_KEY, 'filter' => false],
        ], $overrides);
    }

    private function stripeSpec(array $overrides = []): array
    {
        return array_merge([
            'gateway' => 'stripe',
            'config' => [
                'stripe_secret_key' => 'sk_test_roundtrip',
                'stripe_signing_secret' => self::STRIPE_SECRET,
                'payment_received_notification' => ['mail'],
            ],
            'fixture' => ['mode' => 'create', 'amount' => 9.9],
            'raw_body' => '{"id":"evt_{uniq}","object":"event","api_version":"2024-06-20","created":1750000000,"livemode":false,"type":"checkout.session.completed","data":{"object":{"id":"cs_test_{uniq}","object":"checkout.session","payment_status":"paid","client_reference_id":"{trade_no}","amount_total":990,"currency":"usd","status":"complete"}},"pending_webhooks":1}',
            'sign' => ['algo' => 'stripe', 'secret' => self::STRIPE_SECRET],
        ], $overrides);
    }

    /** Manual 只推进「用户已点付款待确认」(status=1) 的单，夹具按这个前提造. */
    private function pendingManualPayment(): Payment
    {
        $user = User::withoutEvents(fn () => User::create([
            'username' => 'manual-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret'),
        ]));
        $order = Order::create([
            'sn' => 'SNM'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $user->id,
            'amount' => 9.9,
            'origin_amount' => 9.9,
            'status' => 1,
        ]);

        return PaymentHelper::createPayment($user->id, $order->id, 9.9);
    }
}
