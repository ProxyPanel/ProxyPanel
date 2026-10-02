<?php

namespace Tests\Unit\Services;

use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use App\Models\UserCreditLog;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

/**
 * 每人限用的门槛：留空与 0 都是不限，非 0 才比较已用次数。
 */
class CouponServiceTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Goods $goods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create(['username' => 'coupon-'.uniqid().'@example.com', 'passwd' => bcrypt('secret-123')]);
        $this->actingAs($this->user);

        $this->goods = Goods::create(['name' => '测试套餐', 'price' => 9.9]);
    }

    public function test_used_zero_means_unlimited_not_unusable(): void
    {
        $coupon = $this->coupon(['used' => 0]);

        // 已经用过一次也不该被拦：0 是「不限」
        $this->usedOrder($coupon);

        $this->assertInstanceOf(Coupon::class, (new CouponService($coupon->sn))->search($this->goods));
    }

    public function test_used_limit_is_enforced_once_it_is_nonzero(): void
    {
        $coupon = $this->coupon(['used' => 1]);
        $this->usedOrder($coupon);

        $result = (new CouponService($coupon->sn))->search($this->goods);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertSame('fail', $result->getData(true)['status']);
    }

    public function test_second_use_is_allowed_under_a_limit_of_two(): void
    {
        $coupon = $this->coupon(['used' => 2]);
        $this->usedOrder($coupon);

        $this->assertInstanceOf(Coupon::class, (new CouponService($coupon->sn))->search($this->goods));
    }

    public function test_missing_used_key_stays_unlimited(): void
    {
        $coupon = $this->coupon([]);

        $this->assertInstanceOf(Coupon::class, (new CouponService($coupon->sn))->search($this->goods));
    }

    public function test_voucher_credits_once_and_cannot_be_redeemed_again(): void
    {
        $coupon = $this->voucher(20);

        $this->assertTrue((new CouponService($coupon->sn))->charge());
        $this->assertSame(20.0, (float) $this->user->fresh()->credit);
        $this->assertSame(1, $coupon->fresh()->status);

        // 同一张码第二次核销必须整笔失败，不能既加钱又算已用
        $this->assertFalse((new CouponService($coupon->sn))->charge());
        $this->assertSame(20.0, (float) $this->user->fresh()->credit);
    }

    public function test_credit_log_carries_the_balance_before_and_after(): void
    {
        $coupon = $this->voucher(20);

        $this->assertTrue((new CouponService($coupon->sn))->charge());

        $log = UserCreditLog::whereUserId($this->user->id)->orderByDesc('id')->first();
        $this->assertSame(0.0, (float) $log->before);
        $this->assertSame(20.0, (float) $log->after);
        $this->assertSame(20.0, (float) $log->amount);
    }

    public function test_claim_is_granted_once_and_replay_is_refused(): void
    {
        $coupon = $this->voucher(5);

        $this->assertTrue($coupon->claim());
        $this->assertFalse($coupon->claim());
        $this->assertFalse($coupon->fresh()->claim());
    }

    private function voucher(int $value): Coupon
    {
        return Coupon::create([
            'name' => '充值券',
            'sn' => 'RC'.strtoupper(substr(uniqid(), -8)),
            'type' => 3,
            'status' => 0,
            'value' => $value,
            'usable_times' => 1,
            'priority' => 0,
            'start_time' => date('Y-m-d H:i:s', time() - 60),
            'end_time' => date('Y-m-d H:i:s', time() + 3600),
            'limit' => [],
        ]);
    }

    private function coupon(array $limit): Coupon
    {
        return Coupon::create([
            'name' => '测试券',
            'sn' => 'SN'.strtoupper(substr(md5(serialize($limit)), 0, 8)).random_int(1000, 9999),
            'type' => 1,
            'status' => 0,
            'value' => 1,
            'usable_times' => 10,
            'priority' => 0,
            'start_time' => date('Y-m-d H:i:s', time() - 60),
            'end_time' => date('Y-m-d H:i:s', time() + 3600),
            'limit' => $limit,
        ]);
    }

    private function usedOrder(Coupon $coupon): Order
    {
        return Order::create([
            'sn' => 'CU'.strtoupper((string) random_int(100000, 999999)),
            'user_id' => $this->user->id,
            'goods_id' => $this->goods->id,
            'coupon_id' => $coupon->id,
            'amount' => 8.9,
            'status' => 2,
        ]);
    }
}
