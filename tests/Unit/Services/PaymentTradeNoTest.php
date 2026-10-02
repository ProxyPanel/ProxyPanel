<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Utils\Library\PaymentHelper;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;

/**
 * 支付单号：格式、唯一索引与冲突换号。
 */
class PaymentTradeNoTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create(['username' => 'tradeno-'.uniqid().'@example.com', 'passwd' => bcrypt('secret-123')]);
        $this->order = Order::create(['sn' => 'TN'.strtoupper((string) random_int(100000, 999999)), 'user_id' => $this->user->id, 'amount' => 9.9, 'status' => 0]);
    }

    protected function tearDown(): void
    {
        Str::createUlidsUsing(null);

        parent::tearDown();
    }

    public function test_trade_no_is_an_ulid(): void
    {
        $payment = PaymentHelper::createPayment($this->user->id, $this->order->id, 9.9);

        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $payment->trade_no);
        $this->assertSame(990, $payment->getRawOriginal('amount'));
    }

    public function test_trade_no_is_unique_at_the_database_level(): void
    {
        PaymentHelper::createPayment($this->user->id, $this->order->id, 9.9);
        $first = Payment::latest('id')->value('trade_no');

        $this->expectException(UniqueConstraintViolationException::class);
        Payment::create([
            'trade_no' => $first,
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => 1.5,
        ]);
    }

    public function test_a_colliding_trade_no_is_issued_again_instead_of_failing(): void
    {
        // ULID 首字符只能是 0-7（26 字符共 130 位，高 2 位必须为 0）
        $taken = '0'.str_repeat('A', 25);
        $next = '0'.str_repeat('B', 25);

        Payment::create(['trade_no' => $taken, 'user_id' => $this->user->id, 'order_id' => $this->order->id, 'amount' => 1.5]);

        $issued = [$taken, $next];
        Str::createUlidsUsing(function () use (&$issued) {
            $value = array_shift($issued);

            return Ulid::fromString($value ?? '0'.str_repeat('C', 25));
        });

        $payment = PaymentHelper::createPayment($this->user->id, $this->order->id, 9.9);

        $this->assertSame($next, $payment->trade_no);
        $this->assertSame(2, Payment::whereOrderId($this->order->id)->count());
    }
}
