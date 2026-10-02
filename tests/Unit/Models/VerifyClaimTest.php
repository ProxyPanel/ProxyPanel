<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Verify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 令牌认领与作废的状态机：claim 只授予一次，已使用不会被降级成已失效。
 */
class VerifyClaimTest extends TestCase
{
    use DatabaseTransactions;

    private Verify $verify;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create(['username' => 'claim-'.uniqid().'@example.com', 'passwd' => bcrypt('secret-123')]);
        $this->verify = Verify::create(['user_id' => $user->id, 'token' => Verify::hashToken('a-token')]);
    }

    public function test_claim_is_granted_once_and_replay_is_refused(): void
    {
        $this->assertTrue($this->verify->claim());
        $this->assertSame(Verify::STATUS_USED, (int) $this->verify->fresh()->status);

        // 同一条链接并发重放：第二个请求（另一份模型实例）必须拿不到使用权
        $this->assertFalse(Verify::find($this->verify->id)->claim());
    }

    public function test_second_claim_on_the_same_instance_also_fails(): void
    {
        $this->assertTrue($this->verify->claim());
        $this->assertFalse($this->verify->claim());
    }

    public function test_invalidate_marks_only_unused_rows(): void
    {
        $this->verify->invalidate();
        $this->assertSame(Verify::STATUS_INVALID, (int) $this->verify->fresh()->status);

        // 已失效的行不能再被「作废」覆盖，也绝不能被认领
        $this->assertFalse($this->verify->claim());

        $used = Verify::create(['user_id' => $this->verify->user_id, 'token' => Verify::hashToken('b-token')]);
        $used->claim();
        $used->invalidate();

        $this->assertSame(Verify::STATUS_USED, (int) $used->fresh()->status, '已使用不能被降级成已失效');
    }

    public function test_claimed_token_is_no_longer_usable(): void
    {
        $this->assertTrue($this->verify->usable());
        $this->verify->claim();

        $this->assertFalse($this->verify->fresh()->usable());
    }
}
