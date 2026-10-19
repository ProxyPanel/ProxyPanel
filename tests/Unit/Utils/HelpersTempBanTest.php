<?php

namespace Tests\Unit\Utils;

use App\Models\User;
use App\Utils\Helpers;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * 临时封禁的形状只允许有一份：`enable=0` 配 `ban_time`，并留一笔带时长的封禁日志。
 * task:auto 的 unblockUsers() 按 `ban_time < now` 捞回，所以不设 ban_time 就是永久封。
 */
class HelpersTempBanTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::withoutEvents(fn () => User::create([
            'username' => 'ban-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => 20900,
            'enable' => 1,
        ]));
    }

    public function test_it_bans_for_the_given_minutes_and_logs_it(): void
    {
        Helpers::tempBanUser($this->user, 30, '[Test] banned by the unit case');

        $this->user->refresh();
        $this->assertSame(0, (int) $this->user->enable);
        // ban_time 在模型上是时间类 cast（读回来是 Carbon），判不出落库的是整数还是被截断的字符串，所以断言看 DB 原始列值
        $this->assertEqualsWithDelta(time() + 30 * 60, (int) DB::table('user')->where('id', $this->user->id)->value('ban_time'), 5);

        $log = $this->user->banedLogs()->first();
        $this->assertNotNull($log, '封禁必须留一笔 user_baned_log');
        $this->assertSame(30, (int) $log->time);
        $this->assertSame('[Test] banned by the unit case', $log->description);
    }

    public function test_the_ban_is_picked_up_by_the_expiry_query_task_auto_uses(): void
    {
        Helpers::tempBanUser($this->user, 30, '[Test] banned by the unit case');

        // TaskAuto::unblockUsers() 的第一支就是这个查询形状：到期前捞不到，越过 ban_time 一刻就捞得到
        $this->assertSame(0, User::bannedUser()->where('ban_time', '<', time())->whereKey($this->user->id)->count());
        $this->assertSame(1, User::bannedUser()->where('ban_time', '<', time() + 30 * 60 + 1)->whereKey($this->user->id)->count());
    }

    public function test_ban_time_stores_a_date_object_as_an_epoch(): void
    {
        $until = now()->addHour();

        $this->user->update(['ban_time' => $until]);
        $this->assertSame($until->getTimestamp(), (int) $this->user->fresh()->getRawOriginal('ban_time'), '赋 Carbon 被 int 列截断成 2026，就等于封禁当场到期');

        $this->user->update(['ban_time' => (string) $until->getTimestamp()]);
        $this->assertSame($until->getTimestamp(), (int) $this->user->fresh()->getRawOriginal('ban_time'), '数字串要按整数落，不该走日期解析');

        foreach ([null, ''] as $empty) {
            $this->user->update(['ban_time' => $empty]);
            $this->assertNull($this->user->fresh()->getRawOriginal('ban_time'));
        }
    }

    public function test_a_blank_or_zero_duration_still_bans_for_a_positive_time(): void
    {
        // 后台把 0 与空值都存成 NULL（SystemController::setConfig），而那种时长算出的 ban_time 等于当下，
        // 下一分钟就被 unblockUsers() 捞回、再下一分钟又封：时长必须有自己的下限
        foreach ([null, 0, '', '0', 'NaN', -30] as $value) {
            config(['settings.ban_duration' => $value]);

            $minutes = Helpers::banDurationMinutes();
            $this->assertGreaterThanOrEqual(1, $minutes, 'ban_duration='.var_export($value, true).' 算出 '.$minutes.' 分钟');
        }

        config(['settings.ban_duration' => 45]);
        $this->assertSame(45, Helpers::banDurationMinutes(), '有效值原样用，不该被下限改写');

        config(['settings.ban_duration' => null]);
        Helpers::tempBanUser($this->user, Helpers::banDurationMinutes(), '[Test] null duration');
        $this->assertGreaterThan(time(), (int) DB::table('user')->where('id', $this->user->id)->value('ban_time'), 'ban_time 落在当下就等于封禁当场到期');
    }
}
