<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\User\ShopController;
use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use App\Models\UserCreditLog;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 重置流量：扣当前真实余额，扣不到就不清流量。
 */
class TrafficResetChargeTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $goods = Goods::create(['name' => '测试套餐', 'type' => 2, 'price' => 9.9, 'renew' => 1.5, 'days' => 30]);
        $this->user = User::create([
            'username' => 'reset-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'credit' => 2,
            'u' => 1000,
            'd' => 2000,
        ]);
        Order::create([
            'sn' => 'RS'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $this->user->id,
            'goods_id' => $goods->id,
            'amount' => 9.9,
            'status' => 2,
            'is_expire' => 0,
        ]);
        $this->actingAs($this->user);
    }

    public function test_charge_deducts_resets_and_holds_the_row_lock(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $data = (new ShopController)->resetTraffic()->getData(true);
        $sql = implode(' ', array_column(DB::getQueryLog(), 'query'));
        DB::disableQueryLog();

        $this->assertSame('success', $data['status']);
        $this->assertStringContainsString('for update', $sql);

        $fresh = User::find($this->user->id);
        $this->assertSame(0.5, round((float) $fresh->credit, 2));
        $this->assertSame(0, (int) $fresh->u);
        $this->assertSame(0, (int) $fresh->d);

        $log = UserCreditLog::whereUserId($this->user->id)->latest('id')->first();
        $this->assertSame(2.0, round((float) $log->before, 2));
        $this->assertSame(0.5, round((float) $log->after, 2));
        $this->assertSame(-1.5, round((float) $log->amount, 2));
    }

    public function test_reset_without_enough_balance_is_refused(): void
    {
        $this->assertSame('success', (new ShopController)->resetTraffic()->getData(true)['status']);

        // 余额 0.5 低于重置价 1.5：拒付也不该再清流量
        $this->assertSame('fail', (new ShopController)->resetTraffic()->getData(true)['status']);

        $fresh = User::find($this->user->id);
        $this->assertSame(0.5, round((float) $fresh->credit, 2));
        $this->assertSame(1, UserCreditLog::whereUserId($this->user->id)->count());
    }

    /** 登录实例持旧余额时判据读锁内的新值，不能白给一次重置. */
    public function test_stale_instance_cannot_buy_a_free_reset(): void
    {
        // 走模型写才按分入库；另取实例，登录实例保持旧余额
        User::find($this->user->id)->update(['credit' => 0.5]);

        $this->assertSame('fail', (new ShopController)->resetTraffic()->getData(true)['status']);

        $fresh = User::find($this->user->id);
        $this->assertSame(0.5, round((float) $fresh->credit, 2), '不够钱就不该再扣一次');
        $this->assertSame(1000, (int) $fresh->u, '没付钱就不该把流量清掉');
        $this->assertSame(0, UserCreditLog::whereUserId($this->user->id)->count());
    }
}
