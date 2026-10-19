<?php

namespace Tests\Unit\Observers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentConfirm;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\SendQueuedNotifications;
use Queue;
use Tests\TestCase;

/**
 * 后台把订单改成「待确认支付」时的提醒形状。
 *
 * 这条 update 跑在 Admin\LogsController::changeOrderStatus 的 lockForUpdate 事务里：提醒必须在事务内只是入队，
 * 渠道发送（SMTP/Telegram 的往返）落在 worker 里，否则订单行会被按住整个外部调用时长；
 * 发送失败也进 failed_jobs，不会把已提交的状态回滚掉。
 */
class OrderObserverNotifyTest extends TestCase
{
    use DatabaseTransactions;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // 提醒收件人是 id=1 的管理员；渠道为空时 NotificationSender 直接不发，也就不会有入队
        $this->admin();
        config(['settings.payment_confirm_notification' => ['mail']]);

        $user = User::create([
            'username' => 'order-notice-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => 30000 + random_int(1, 9999),
        ]);
        $order = Order::create([
            'sn' => 'ON'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $user->id,
            'amount' => 6,
            'origin_amount' => 6,
            'status' => 3,
        ]);
        // 通知构造时要把 payment.id 编进签名链接，缺这行会在构造阶段就炸
        Payment::create([
            'trade_no' => 'NT'.strtoupper(substr(md5($order->sn), 0, 12)),
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 600,
        ]);

        $this->order = $order;
    }

    public function test_the_confirm_notice_is_only_enqueued_inside_the_row_lock(): void
    {
        Queue::fake();

        DB::transaction(function (): void {
            $locked = Order::whereKey($this->order->id)->lockForUpdate()->first();

            $locked->update(['is_expire' => 0, 'expired_at' => null, 'status' => 1]);

            // 事务里只该看到一次入队，渠道发送不在这里
            Queue::assertPushed(SendQueuedNotifications::class, 1);
        });

        Queue::assertPushed(
            SendQueuedNotifications::class,
            fn (SendQueuedNotifications $job): bool => $job->notification instanceof PaymentConfirm
        );
    }

    public function test_the_status_change_still_commits_and_is_idempotent(): void
    {
        Queue::fake();

        DB::transaction(function (): void {
            Order::whereKey($this->order->id)->lockForUpdate()->first()->update(['status' => 1]);
        });

        $this->assertSame(1, (int) Order::find($this->order->id)->status);
    }
}
