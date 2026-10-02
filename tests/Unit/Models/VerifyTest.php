<?php

namespace Tests\Unit\Models;

use App\Models\Verify;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * 校验令牌的时效判据：未使用且未超时才可用，TTL 1800 秒。
 */
class VerifyTest extends TestCase
{
    private const FROZEN_NOW = '2026-09-30 12:00:00';

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_token_is_stored_as_an_unpredictable_sha256(): void
    {
        $token = '0123456789abcdef'.str_repeat('A', 32);

        $hash = Verify::hashToken($token);

        $this->assertNotSame($token, $hash);
        $this->assertSame(64, strlen($hash));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $hash);
        $this->assertSame($hash, Verify::hashToken($token));
        $this->assertNotSame($hash, Verify::hashToken($token.'x'));
    }

    public function test_a_fresh_unused_link_is_usable(): void
    {
        Carbon::setTestNow(self::FROZEN_NOW);

        $this->assertTrue($this->verify('2026-09-30 11:45:00', Verify::STATUS_UNUSED)->usable());
    }

    /**
     * TTL 边界：刚好半小时即作废，而不是再过一秒才作废。
     */
    public function test_usable_until_the_ttl_expires_and_not_after(): void
    {
        Carbon::setTestNow(self::FROZEN_NOW);

        $this->assertTrue($this->verify('2026-09-30 11:30:01', Verify::STATUS_UNUSED)->usable());
        $this->assertFalse($this->verify('2026-09-30 11:30:00', Verify::STATUS_UNUSED)->usable());
        $this->assertFalse($this->verify('2026-09-30 10:00:00', Verify::STATUS_UNUSED)->usable());
    }

    /**
     * 已使用与已作废的记录一律不可用，超时判据排在状态之后。
     */
    public function test_used_and_invalid_links_are_never_usable(): void
    {
        Carbon::setTestNow(self::FROZEN_NOW);

        foreach ([Verify::STATUS_USED, Verify::STATUS_INVALID] as $status) {
            $this->assertFalse($this->verify('2026-09-30 11:59:00', $status)->usable());
        }
    }

    private function verify(string $createdAt, int $status): Verify
    {
        return (new Verify)->newFromBuilder([
            'id' => 1,
            'type' => 1,
            'user_id' => 1,
            'token' => Verify::hashToken('whatever'),
            'status' => $status,
            'created_at' => $createdAt,
        ]);
    }
}
