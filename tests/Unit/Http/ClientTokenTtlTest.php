<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\Api\Client\AuthController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 客户端 token 有效期唯一来源是 config('sanctum.client_token_ttl')：留空不签过期令牌；
 * 绝不能回落到 sanctum.expiration（那会按 created_at 作废部署前签发的所有令牌）。
 */
class ClientTokenTtlTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        (new AuthController(request()))->setClient('clash');
    }

    protected function tearDown(): void
    {
        (new AuthController(request()))->setClient('clash');
        parent::tearDown();
    }

    public function test_ttl_writes_expires_at_on_issued_token(): void
    {
        config(['sanctum.client_token_ttl' => 60]);
        $user = $this->user();

        $resp = $this->postJson('/api/v1/login', ['username' => $user->username, 'password' => 'pass1234']);
        $resp->assertJsonPath('status', 'success');

        $expires = $this->latestExpiresAt($user);
        $this->assertNotNull($expires);
        $this->assertTrue(
            $expires->between(now()->addMinutes(58), now()->addMinutes(62)),
            "expires_at 应约等于 now+60min，实际 {$expires}"
        );
    }

    public function test_empty_ttl_issues_non_expiring_token(): void
    {
        config(['sanctum.client_token_ttl' => null]);
        $user = $this->user();

        $this->postJson('/api/v1/login', ['username' => $user->username, 'password' => 'pass1234'])
            ->assertJsonPath('status', 'success');

        $this->assertNull($this->latestExpiresAt($user));
    }

    public function test_sanctum_expiration_is_not_used_as_token_ttl(): void
    {
        // 若误用 sanctum.expiration，expires_at 会变成 now+5min 而非 ttl 的 60min
        config(['sanctum.client_token_ttl' => 60, 'sanctum.expiration' => 5]);
        $user = $this->user();

        $this->postJson('/api/v1/login', ['username' => $user->username, 'password' => 'pass1234'])
            ->assertJsonPath('status', 'success');

        $expires = $this->latestExpiresAt($user);
        $this->assertTrue(
            $expires->between(now()->addMinutes(58), now()->addMinutes(62)),
            'token TTL 必须取自 client_token_ttl，而非 sanctum.expiration'
        );
    }

    private function user(): User
    {
        return User::create([
            'username' => 'ttl-'.uniqid().'@example.com',
            'password' => 'pass1234', // User 的 setPasswordAttribute 会再哈希一次，这里传明文
            'passwd' => 'pass1234',
            'port' => 33000 + random_int(1, 999),
            'transfer_enable' => 10 * GiB,
            'status' => 1,
        ]);
    }

    private function latestExpiresAt(User $user)
    {
        $raw = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $user->id)
            ->latest('id')
            ->value('expires_at');

        return $raw ? Carbon::parse($raw) : null;
    }
}
