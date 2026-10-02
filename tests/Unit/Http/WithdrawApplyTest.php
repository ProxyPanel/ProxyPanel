<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\User\AffiliateController;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use App\Models\User;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 提现申请：一个用户同时只有一笔在审申请，同一批佣金只被引用一次。
 */
class WithdrawApplyTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'username' => 'wd-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'expired_at' => now()->addMonth(),
        ]);
        $this->actingAs($this->user);

        // 最低提现额 10 元，攒 15 元佣金
        config(['settings.referral_money' => 10]);

        foreach (range(1, 3) as $ignored) {
            ReferralLog::create([
                'inviter_id' => $this->user->id,
                'invitee_id' => $this->user->id,
                'amount' => 100,
                'commission' => 5,
                'status' => 0,
            ]);
        }
    }

    public function test_pending_apply_is_checked_under_the_row_lock(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $first = (new AffiliateController)->withdraw()->getData(true);
        $sql = implode(' ', array_column(DB::getQueryLog(), 'query'));
        DB::disableQueryLog();

        $this->assertSame('success', $first['status']);
        $this->assertStringContainsString('for update', $sql);

        $apply = ReferralApply::whereUserId($this->user->id)->sole();
        $this->assertCount(3, $apply->link_logs);
        $this->assertSame(15.0, round((float) $apply->amount, 2));

        // 第二笔必须被拒，不能又建一条把同一批佣金再要走一次
        $this->assertSame('fail', (new AffiliateController)->withdraw()->getData(true)['status']);
        $this->assertSame(1, ReferralApply::whereUserId($this->user->id)->count());
    }

    public function test_apply_below_the_minimum_is_refused(): void
    {
        config(['settings.referral_money' => 100]);

        $data = (new AffiliateController)->withdraw()->getData(true);

        $this->assertSame('fail', $data['status']);
        $this->assertSame(0, ReferralApply::whereUserId($this->user->id)->count());
    }
}
