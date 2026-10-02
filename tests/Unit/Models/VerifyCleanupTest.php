<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Verify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 令牌清理：超时的未用行置失效，过保留期的删除。
 */
class VerifyCleanupTest extends TestCase
{
    use DatabaseTransactions;

    private int $uid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uid = User::create(['username' => 'verify-'.uniqid().'@example.com', 'passwd' => bcrypt('secret-123')])->id;
    }

    public function test_unused_token_past_its_ttl_is_invalidated_but_kept(): void
    {
        $verify = $this->verify(Verify::STATUS_UNUSED, now()->subSeconds(Verify::TTL + 1));

        Verify::cleanup();

        $this->assertSame(Verify::STATUS_INVALID, (int) $verify->fresh()->status);
    }

    public function test_fresh_unused_token_is_untouched(): void
    {
        $verify = $this->verify(Verify::STATUS_UNUSED, now()->subMinutes(1));

        Verify::cleanup();

        $this->assertSame(Verify::STATUS_UNUSED, (int) $verify->fresh()->status);
    }

    public function test_dead_token_past_the_retention_window_is_deleted(): void
    {
        $pruned = $this->verify(Verify::STATUS_USED, now()->subDays(Verify::RETAIN_DAYS + 1));
        $kept = $this->verify(Verify::STATUS_INVALID, now()->subDays(1));

        Verify::cleanup();

        $this->assertNull(Verify::find($pruned->id));
        $this->assertNotNull(Verify::find($kept->id));
    }

    private function verify(int $status, $createdAt): Verify
    {
        return Verify::create([
            'type' => 1,
            'user_id' => $this->uid,
            'token' => Verify::hashToken('t'.uniqid()),
            'status' => $status,
            'created_at' => $createdAt,
        ]);
    }
}
