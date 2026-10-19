<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 余额变动：累加与「够不够」的判据都发生在数据库侧。
 */
class UserCreditTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'username' => 'credit-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'credit' => 10,
        ]);
    }

    public function test_amount_reaches_the_column_as_fen(): void
    {
        $this->assertTrue($this->user->updateCredit(1.5));

        $this->assertSame(1150, (int) User::find($this->user->id)->getRawOriginal('credit'));
    }

    public function test_two_grants_to_the_same_account_both_land(): void
    {
        $first = User::find($this->user->id);
        $second = User::find($this->user->id);

        $this->assertTrue($first->updateCredit(5));
        $this->assertTrue($second->updateCredit(5));

        $this->assertSame(20.0, round((float) User::find($this->user->id)->credit, 2));
    }

    public function test_two_deductions_from_the_same_account_both_land(): void
    {
        $first = User::find($this->user->id);
        $second = User::find($this->user->id);

        $this->assertTrue($first->updateCredit(-3));
        $this->assertTrue($second->updateCredit(-3));

        $this->assertSame(4.0, round((float) User::find($this->user->id)->credit, 2));
    }

    public function test_the_second_deduction_is_refused_when_only_one_fits(): void
    {
        User::whereKey($this->user->id)->update(['credit' => 300]); // 3 元

        $first = User::find($this->user->id);
        $second = User::find($this->user->id);

        $this->assertTrue($first->updateCredit(-2));
        $this->assertFalse($second->updateCredit(-2), '余额只够一笔，另一笔必须被挡掉');
        $this->assertSame(1.0, round((float) User::find($this->user->id)->credit, 2), '不够钱不能被静默截断成 0');
    }

    public function test_zero_change_is_a_successful_noop(): void
    {
        // 充值券面额允许填 0，它仍是一次成功的核销
        $this->assertTrue($this->user->updateCredit(0));

        $this->assertSame(1000, (int) User::find($this->user->id)->getRawOriginal('credit'));
    }

    public function test_in_memory_balance_tracks_the_write(): void
    {
        $this->user->updateCredit(-6);

        // 调用方拿它作为余额变动日志的 after
        $this->assertSame(4.0, round((float) $this->user->credit, 2));
    }
}
