<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\Admin\LogsController;
use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use App\Models\UserCreditLog;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * 后台改订单状态：0→2 的履约只走一次。
 */
class OrderStatusChangeTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // 充值类订单（无商品）：履约就是往余额加钱，便于观察重复
        $this->user = User::create(['username' => 'status-'.uniqid().'@example.com', 'passwd' => bcrypt('secret-123'), 'credit' => 0]);
        $this->order = Order::create([
            'sn' => 'ST'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $this->user->id,
            'amount' => 6,
            'origin_amount' => 6,
            'status' => 0,
        ]);
    }

    public function test_fulfills_once_under_the_row_lock(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->assertSame('success', $this->change($this->order->id, 2)->getData(true)['status']);
        $sql = implode(' ', array_column(DB::getQueryLog(), 'query'));
        DB::disableQueryLog();

        $this->assertStringContainsString('for update', $sql);

        // 重复点同一状态：幂等成功，不再加钱
        $this->assertSame('success', $this->change($this->order->id, 2)->getData(true)['status']);

        $this->assertSame(6.0, round((float) User::find($this->user->id)->credit, 2));
        $this->assertSame(1, UserCreditLog::whereUserId($this->user->id)->count());
        $this->assertSame(2, (int) Order::find($this->order->id)->status);
    }

    public function test_prepay_order_is_not_fake_activated(): void
    {
        $goods = Goods::create(['name' => '测试套餐', 'type' => 2, 'price' => 6, 'days' => 30, 'status' => 1]);
        $prepay = Order::create([
            'sn' => 'PP'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $this->user->id,
            'goods_id' => $goods->id,
            'amount' => 6,
            'status' => 3,
        ]);
        Order::create([
            'sn' => 'PA'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $this->user->id,
            'goods_id' => $goods->id,
            'amount' => 6,
            'status' => 2,
            'expired_at' => now()->addMonth(),
        ]);

        $this->assertSame('fail', $this->change($prepay->id, 2)->getData(true)['status']);
        $this->assertSame(3, (int) Order::find($prepay->id)->status);
    }

    private function change(int $oid, int $status): JsonResponse
    {
        return (new LogsController)->changeOrderStatus(Request::create('/admin/logs/order/status', 'POST', [
            'oid' => $oid,
            'status' => $status,
        ]));
    }
}
