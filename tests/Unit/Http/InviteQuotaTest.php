<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\User\InviteController;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 邀请名额：一份名额只出一个码。
 */
class InviteQuotaTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'username' => 'quota-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'invite_num' => 1,
        ]);
        $this->actingAs($this->user);

        config(['settings.user_invite_days' => 30]);
    }

    public function test_one_quota_generates_one_code(): void
    {
        $this->assertSame('success', (new InviteController)->store()->getData(true)['status']);
        $this->assertSame(0, (int) User::find($this->user->id)->invite_num);

        $this->assertSame('fail', (new InviteController)->store()->getData(true)['status']);
        $this->assertSame(1, Invite::whereInviterId($this->user->id)->count());
    }

    public function test_stale_instance_cannot_overdraw_the_quota(): void
    {
        $this->assertSame('success', (new InviteController)->store()->getData(true)['status']);

        // 库里已无名额，实例还留着生成前的旧值
        $this->user->invite_num = 1;

        $this->assertSame('fail', (new InviteController)->store()->getData(true)['status']);
        $this->assertSame(1, Invite::whereInviterId($this->user->id)->count());
        $this->assertSame(0, (int) User::find($this->user->id)->invite_num);
    }
}
