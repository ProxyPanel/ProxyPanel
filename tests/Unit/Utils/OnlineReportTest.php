<?php

namespace Tests\Unit\Utils;

use App\Utils\Hysteria2\OnlineReport;
use PHPUnit\Framework\TestCase;

/**
 * 这里继承裸 PHPUnit 的 TestCase 而不是 Tests\TestCase：
 * phpunit.xml 的 bootstrap 只有 vendor/autoload.php，而启动 Laravel 容器时
 * SettingServiceProvider 会读取 config 表，导致每个测试都需要一个可达的数据库。
 * 本文件只验证 OnlineReport 的纯判定逻辑，不依赖容器，所以能真正跑起来。
 */
class OnlineReportTest extends TestCase
{
    /**
     * @dataProvider providerOnlineResponses
     */
    public function test_merge_combines_every_reachable_endpoint(array $responses, bool $reachable, array $expected): void
    {
        $report = OnlineReport::merge($responses);

        $this->assertSame($reachable, $report['reachable']);
        $this->assertSame($expected, $report['user_ids']);
    }

    public static function providerOnlineResponses(): array
    {
        return [
            '单个地址' => [[[5 => 2, 7 => 1]], true, [5, 7]],
            '多IP地址取并集而不是相加' => [[[5 => 2, 7 => 1], [7 => 3, 9 => 1]], true, [5, 7, 9]],
            '空映射：节点正常但无人在线' => [[[]], true, []],
            '全部不可达' => [[null, null], false, []],
            '部分不可达' => [[null, [5 => 1]], true, [5]],
            '非数组响应视为不可达' => [['<html></html>'], false, []],
            '非面板用户ID的键被忽略' => [[['abc' => 1, '9' => 2]], true, [9]],
            '没有任何响应' => [[], false, []],
        ];
    }

    /**
     * @dataProvider providerSplit
     */
    public function test_split_separates_online_from_revoked(array $userIds, array $eligibleIds, array $expected): void
    {
        $this->assertSame($expected, OnlineReport::split($userIds, $eligibleIds));
    }

    public static function providerSplit(): array
    {
        return [
            '全部放行' => [[1, 2], [1, 2, 3], ['online' => [1, 2], 'revoked' => []]],
            // 被禁用、过期、失去节点权限的用户不在准入名单里，必须被踢掉而不是继续计入在线人数
            '被禁用' => [[1, 2], [1], ['online' => [1], 'revoked' => [2]]],
            '全部失去资格' => [[1, 2], [], ['online' => [], 'revoked' => [1, 2]]],
            '节点无人在线' => [[], [1, 2], ['online' => [], 'revoked' => []]],
        ];
    }

    /**
     * @dataProvider providerUptime
     */
    public function test_uptime_only_accumulates_while_observations_stay_continuous(?int $previousUptime, ?int $previousLogTime, int $now, int $expected): void
    {
        $this->assertSame($expected, OnlineReport::uptime($previousUptime, $previousLogTime, $now));
    }

    public static function providerUptime(): array
    {
        $continuity = OnlineReport::UPTIME_CONTINUITY;
        $seed = OnlineReport::UPTIME_SEED;

        return [
            '首次观测' => [null, null, 1000, $seed],
            '窗口内续算' => [100, 1000, 1060, 160],
            '恰好等于窗口仍然续算' => [100, 1000, 1000 + $continuity, 100 + $continuity],
            '超过窗口视为中间断过线' => [100, 1000, 1001 + $continuity, $seed],
            '时间回拨视为断线' => [100, 1000, 999, $seed],
        ];
    }
}
