<?php

namespace Tests\Unit\Http;

use App\Models\Invite;
use App\Models\User;
use App\Utils\IP;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * 注册：一个邀请码只换一个账号，每 IP 名额只数成功建号。
 */
class RegisterClaimTest extends TestCase
{
    use DatabaseTransactions;

    private Invite $code;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'settings.is_register' => 1,
            'settings.is_captcha' => 0,
            'settings.is_invite_register' => 1,
            'settings.is_activate_account' => 0,
            'settings.register_ip_limit' => 5,
            'settings.max_port' => 50000,
            'settings.default_traffic' => 100,
            'settings.default_days' => 30,
            'settings.username_type' => 'email',
            'settings.referral_traffic' => 0,
            'settings.user_invite_days' => 30,
        ]);

        $inviter = User::create([
            'username' => 'inv-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'expired_at' => now()->addYear(),
        ]);
        $this->code = Invite::create([
            'inviter_id' => $inviter->id,
            'code' => 'RG'.strtoupper(substr(uniqid(), -8)),
            'status' => 0,
            'dateline' => now()->addDays(30),
        ]);
    }

    public function test_invite_code_is_consumed_by_exactly_one_account(): void
    {
        $first = $this->email();
        $this->register($first)->assertRedirect();
        $this->assertTrue(DB::table('user')->where('username', $first)->exists());
        $this->assertSame(1, (int) $this->code->fresh()->status);
        $this->assertNotNull($this->code->fresh()->invitee_id);

        // 同一个码再注册：不再建号
        $second = $this->email();
        $this->register($second)->assertRedirect();
        $this->assertFalse(DB::table('user')->where('username', $second)->exists());
    }

    public function test_over_limit_registration_is_refused_without_keeping_the_reservation(): void
    {
        config(['settings.register_ip_limit' => 1]);
        $counter = 'register_times_'.md5(IP::getClientIp());

        $this->register($this->email())->assertRedirect();
        $this->assertSame(1, (int) cache()->get($counter));

        $blocked = $this->email();
        $this->register($blocked)->assertRedirect();

        $this->assertFalse(DB::table('user')->where('username', $blocked)->exists(), '超出每 IP 每日名额的注册要被挡住');
        // 被挡住的请求不留计数，免得挤掉同一出口 IP 后面的用户
        $this->assertSame(1, (int) cache()->get($counter));
    }

    private function email(): string
    {
        return 'r'.uniqid().'@example.com';
    }

    private function register(string $username): TestResponse
    {
        return $this->withSession(['register_token' => 'tok'])->post('/register', [
            'nickname' => '新用户',
            'username' => $username,
            'password' => 'secret-123',
            'password_confirmation' => 'secret-123',
            'term' => 1,
            'code' => $this->code->code,
            'register_token' => 'tok',
        ]);
    }
}
