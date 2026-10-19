<?php

namespace Tests\Unit\Services;

use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use App\Models\UserDataModifyLog;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 流量包激活：变动日志要记库里真实的写前/写后值。
 */
class PackageTrafficLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Goods $goods;

    protected function setUp(): void
    {
        parent::setUp();

        // goods.traffic 按 MiB 倍数存，落库时乘 MiB
        $this->goods = Goods::create(['name' => '流量包-'.uniqid(), 'type' => 1, 'price' => 6, 'traffic' => 1, 'status' => 1]);
        $this->user = User::create([
            'username' => 'pkg-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'transfer_enable' => 10 * GiB,
        ]);
    }

    public function test_grants_the_purchased_traffic(): void
    {
        $this->assertTrue((new OrderService($this->order()))->receivedPayment());

        $this->assertSame(10 * GiB + MiB, (int) User::whereKey($this->user->id)->value('transfer_enable'));
    }

    public function test_logs_the_database_values_when_the_snapshot_is_stale(): void
    {
        $service = new OrderService($this->order()); // 服务拿到的是履约前水合的账号快照

        // 快照之后账号又被加过流量（签到、邀请返利、另一张订单都会这样）
        User::whereKey($this->user->id)->increment('transfer_enable', 5 * GiB);

        $this->assertTrue($service->receivedPayment());

        $after = (int) User::whereKey($this->user->id)->value('transfer_enable');
        $log = UserDataModifyLog::whereUserId($this->user->id)->latest('id')->first();

        $this->assertSame(15 * GiB + MiB, $after);
        $this->assertSame($after, (int) $log->getRawOriginal('after'));
        $this->assertSame($after - MiB, (int) $log->getRawOriginal('before'));
    }

    private function order(): Order
    {
        return Order::create([
            'sn' => 'PK'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $this->user->id,
            'goods_id' => $this->goods->id,
            'amount' => 6,
            'origin_amount' => 6,
            'status' => 3,
            'pay_way' => 'credit',
        ]);
    }
}
