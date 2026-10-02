<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\PaymentController;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * 重复提交同一件商品：只建一张单、只扣一次钱。
 */
class DuplicatePurchaseTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Goods $goods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'username' => 'buy-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'credit' => 20,
        ]);
        $this->goods = Goods::create(['name' => '测试套餐', 'type' => 2, 'price' => 6, 'days' => 30, 'status' => 1]);
        $this->actingAs($this->user);
    }

    public function test_repeat_submit_is_refused_without_creating_a_second_order(): void
    {
        $this->assertSame('success', $this->purchase()->getData(true)['status']);
        $this->assertSame(1, Order::whereUserId($this->user->id)->count());
        $this->assertSame(14.0, round((float) User::find($this->user->id)->credit, 2));

        $second = $this->purchase();

        $this->assertSame('fail', $second->getData(true)['status']);
        $this->assertSame(trans('auth.error.repeat_request'), $second->getData(true)['message']);
        $this->assertSame(1, Order::whereUserId($this->user->id)->count(), '重复提交不该再建一张单');
        $this->assertSame(14.0, round((float) User::find($this->user->id)->credit, 2), '更不该再扣一次钱');
    }

    public function test_a_different_goods_is_not_blocked_by_the_claim(): void
    {
        $this->assertSame('success', $this->purchase()->getData(true)['status']);

        // 换商品是另一笔意图，不受认领键影响
        $other = Goods::create(['name' => '另一个套餐', 'type' => 2, 'price' => 9, 'days' => 30, 'status' => 1]);

        $this->assertSame('success', $this->purchase($other)->getData(true)['status']);
        $this->assertSame(2, Order::whereUserId($this->user->id)->count());
        $this->assertSame(5.0, round((float) User::find($this->user->id)->credit, 2));
    }

    public function test_the_claim_is_per_user(): void
    {
        $this->assertSame('success', $this->purchase()->getData(true)['status']);

        $other = User::create(['username' => 'buyer-'.uniqid().'@example.com', 'passwd' => bcrypt('secret-123'), 'credit' => 20]);
        $this->actingAs($other);

        $this->assertSame('success', $this->purchase()->getData(true)['status']);
        $this->assertSame(1, Order::whereUserId($other->id)->count());
    }

    /** 在线网关：钱在对方站点付，重复提交不多扣款也不多建单. */
    public function test_repeat_submit_on_an_online_gateway_creates_no_second_payment(): void
    {
        config([
            'settings.is_onlinePay' => 1,
            'settings.epay_url' => 'https://epay.example/',
            'settings.epay_mch_id' => '1001',
            'settings.epay_key' => 'unit-test-key',
        ]);

        $this->assertSame('success', $this->purchase(null, 'epay')->getData(true)['status']);
        $this->assertSame(1, Order::whereUserId($this->user->id)->count());
        $this->assertSame(1, Payment::whereUserId($this->user->id)->count());
        $this->assertSame(0, (int) Order::whereUserId($this->user->id)->value('status'));
        // 没在网关付过，余额不动
        $this->assertSame(20.0, round((float) User::find($this->user->id)->credit, 2));

        $this->assertSame('fail', $this->purchase(null, 'epay')->getData(true)['status']);
        $this->assertSame(1, Order::whereUserId($this->user->id)->count(), '不该再建一张一模一样的单');
        $this->assertSame(1, Payment::whereUserId($this->user->id)->count(), '也不该再多发一个支付号');
    }

    private function purchase(?Goods $goods = null, string $method = 'credit'): JsonResponse
    {
        return (new PaymentController)->purchase(Request::create('/user/payment/purchase', 'POST', [
            'goods_id' => ($goods ?? $this->goods)->id,
            'method' => $method,
            'pay_type' => 1,
        ]));
    }
}
