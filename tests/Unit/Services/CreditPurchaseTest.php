<?php

namespace Tests\Unit\Services;

use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use App\Models\UserCreditLog;
use App\Utils\Payments\Credit;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * 余额购买：判据在用户行锁里，钱不够就整笔不做。
 */
class CreditPurchaseTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Goods $goods;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->goods = Goods::create(['name' => '加油包', 'type' => 1, 'price' => 6, 'traffic' => 1, 'status' => 1]);
        $this->user = User::create([
            'username' => 'credit-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'credit' => 10,
        ]);
        $this->order = $this->pendingOrder();
        $this->actingAs($this->user);
    }

    public function test_deducts_under_the_row_lock_and_completes(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $data = $this->purchase()->getData(true);
        $sql = implode(' ', array_column(DB::getQueryLog(), 'query'));
        DB::disableQueryLog();

        $this->assertSame('success', $data['status']);
        $this->assertStringContainsString('for update', $sql);

        $this->assertSame(4.0, round((float) User::find($this->user->id)->credit, 2));
        $this->assertSame(2, (int) Order::find($this->order->id)->status);

        $log = UserCreditLog::whereUserId($this->user->id)->latest('id')->first();
        $this->assertSame(10.0, round((float) $log->before, 2));
        $this->assertSame(4.0, round((float) $log->after, 2));
        $this->assertSame(-6.0, round((float) $log->amount, 2));
    }

    public function test_insufficient_balance_is_refused_without_touching_anything(): void
    {
        // 登录实例与订单都按 6 元下好了，但库里只剩 3 元：并发把另一笔扣掉了
        User::find($this->user->id)->update(['credit' => 3]);

        $data = $this->purchase()->getData(true);

        $this->assertSame('fail', $data['status']);
        $this->assertSame(3.0, round((float) User::find($this->user->id)->credit, 2), '不够钱不该写库');
        $this->assertSame(0, (int) Order::find($this->order->id)->status, '没付钱不该把订单判成已支付');
        $this->assertSame(0, UserCreditLog::whereUserId($this->user->id)->count());
    }

    private function pendingOrder(): Order
    {
        return Order::create([
            'sn' => 'CR'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $this->user->id,
            'goods_id' => $this->goods->id,
            'amount' => 6,
            'origin_amount' => 6,
            'status' => 0,
            'pay_way' => 'credit',
        ]);
    }

    private function purchase(): JsonResponse
    {
        return (new Credit)->purchase(Request::create('/user/payment/purchase', 'POST', [
            'id' => $this->order->id,
            'goods_id' => $this->goods->id,
        ]));
    }
}
