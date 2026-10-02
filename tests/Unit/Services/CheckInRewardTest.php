<?php

namespace Tests\Unit\Services;

use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\UserController;
use App\Models\User;
use App\Models\UserDataModifyLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * 签到：并发两笔流量变动都要落库，一次签到只领一次。
 */
class CheckInRewardTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'username' => 'checkin-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'transfer_enable' => 10 * MiB,
        ]);
        $this->actingAs($this->user);

        // 奖励区间收成定值，流量增量就可断言；签到间隔 1 分钟
        config(['settings.checkin_interval' => 1, 'settings.checkin_reward' => 1, 'settings.checkin_reward_max' => 1]);
    }

    public function test_both_concurrent_grants_land(): void
    {
        $base = $this->user->transfer_enable;

        // 两条请求各握同一行的副本：读回来加再写会盖掉先提交那笔
        $first = User::find($this->user->id);
        $second = User::find($this->user->id);
        $first->incrementData(MiB);
        $second->incrementData(MiB * 2);

        $this->assertSame($base + MiB * 3, (int) User::find($this->user->id)->transfer_enable);
    }

    public function test_zero_reward_is_still_a_successful_grant(): void
    {
        $this->assertTrue($this->user->incrementData(0));
    }

    public function test_check_in_grants_once_and_logs_the_real_balance_pair(): void
    {
        $before = $this->user->transfer_enable;

        $this->assertSame('success', (new UserController)->checkIn()->getData(true)['status']);
        $this->assertSame($before + MiB, (int) User::find($this->user->id)->transfer_enable);

        $log = UserDataModifyLog::whereUserId($this->user->id)->latest('id')->first();
        $this->assertSame($before, (int) $log->getRawOriginal('before'));
        $this->assertSame($before + MiB, (int) $log->getRawOriginal('after'));

        // 再点一次只回「已签到」，不再发一份
        $second = (new UserController)->checkIn()->getData(true);
        $this->assertSame(trans('user.home.attendance.done'), $second['message']);
        $this->assertSame($before + MiB, (int) User::find($this->user->id)->transfer_enable);
    }

    public function test_client_check_in_shares_the_claim(): void
    {
        $this->assertSame('success', (new UserController)->checkIn()->getData(true)['status']);

        $request = Request::create('/api/v1/doCheckIn', 'POST');
        $this->app->instance('request', $request);

        // 两个入口共用同一把键：面板签过客户端就不再发
        $this->assertSame(0, (new ClientController($request))->checkIn($request)->getData(true)['ret']);
    }
}
