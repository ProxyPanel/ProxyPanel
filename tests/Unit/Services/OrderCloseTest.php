<?php

namespace Tests\Unit\Services;

use App\Models\Coupon;
use App\Models\CouponLog;
use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 关闭订单：重复关闭幂等，券只退一次。
 */
class OrderCloseTest extends TestCase
{
    use DatabaseTransactions;

    private Order $order;

    private Coupon $coupon;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create(['username' => 'close-'.uniqid().'@example.com', 'passwd' => bcrypt('secret-123')]);
        $goods = Goods::create(['name' => '加油包', 'type' => 1, 'price' => 6, 'traffic' => 1, 'status' => 1]);
        $this->coupon = Coupon::create([
            'name' => '测试券',
            'sn' => 'CL'.strtoupper(substr(uniqid(), -8)),
            'type' => 1,
            'status' => 1, // 已被这张订单核销
            'value' => 1,
            'usable_times' => 0,
            'priority' => 0,
            'start_time' => date('Y-m-d H:i:s', time() - 60),
            'end_time' => date('Y-m-d H:i:s', time() + 3600),
            'limit' => [],
        ]);
        $this->order = Order::create([
            'sn' => 'CL'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $user->id,
            'goods_id' => $goods->id,
            'coupon_id' => $this->coupon->id,
            'amount' => 6,
            'status' => 0,
        ]);
    }

    public function test_second_close_is_idempotent_and_returns_the_coupon_once(): void
    {
        // 两条并发请求各握一份 status=0 的旧副本
        $first = Order::find($this->order->id);
        $second = Order::find($this->order->id);

        $this->assertTrue($first->close());
        $this->assertTrue($second->close(), '已关闭的订单再关一次应当幂等');

        $coupon = $this->coupon->fresh();
        $this->assertSame(1, (int) $coupon->usable_times, '券次数只能退回一次');
        $this->assertSame(0, (int) $coupon->status);
        $this->assertSame(1, CouponLog::whereCouponId($coupon->id)->count(), '回退日志也该只有一条');
    }
}
