<?php

namespace Tests\Unit\Console;

use App\Models\Node;
use App\Models\NodeDailyDataFlow;
use App\Models\NodeHourlyDataFlow;
use App\Models\User;
use App\Models\UserDailyDataFlow;
use App\Models\UserDataFlowLog;
use App\Models\UserHourlyDataFlow;
use App\Notifications\DataAnomaly;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * task:hourly / task:daily 的流量聚合契约：库侧 SUM 合并同一 (用户,节点) 的多次上报，
 * 活跃过滤只作用于用户表、节点表统计全部用户。命令用 date()/strtotime() 取墙钟窗口，
 * 测试按同一公式算出窗口后立即执行（跨整点/午夜的极小概率会让窗口错位）。
 */
class TaskTrafficStatisticsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Queue::fake(); // task:daily 的 expireUser 会经 UserObserver 下发 VNet/Hysteria2 Job，测试里不真跑
        config([
            'settings.data_anomaly_notification' => null,
            'settings.reset_traffic' => 0,
            'settings.auto_release_port' => 0,
            'settings.tasks_chunk' => 3000,
            'settings.tasks_close' => ['tickets' => '-24 hours'],
        ]);
    }

    public function test_hourly_collapses_repeated_reports_and_writes_a_total_row(): void
    {
        ['start' => $start, 'end' => $end, 'created_at' => $createdAt] = $this->hourWindow();

        $nodeA = $this->node();
        $nodeB = $this->node();
        $user = $this->user();

        $this->log($user->id, $nodeA->id, 100, 200, $start + 10);
        $this->log($user->id, $nodeA->id, 50, 60, $end - 10); // 同 (用户,节点) 的第二次上报
        $this->log($user->id, $nodeB->id, 7, 9, $start + 300);

        Artisan::call('task:hourly');

        $this->assertFlow(UserHourlyDataFlow::where('user_id', $user->id)->where('node_id', $nodeA->id)->first(), 150, 260);
        $this->assertFlow(UserHourlyDataFlow::where('user_id', $user->id)->where('node_id', $nodeB->id)->first(), 7, 9);

        $total = UserHourlyDataFlow::where('user_id', $user->id)->whereNull('node_id')->first();
        $this->assertFlow($total, 157, 269);
        $this->assertSame($createdAt, (string) $total->created_at);
    }

    public function test_hourly_user_rows_only_cover_active_users_but_node_rows_cover_everyone(): void
    {
        ['start' => $start] = $this->hourWindow();

        $node = $this->node();
        $active = $this->user(['status' => 1, 'enable' => 1]);
        $disabled = $this->user(['status' => 1, 'enable' => 0]);
        $banned = $this->user(['status' => -1, 'enable' => 1]);

        $this->log($active->id, $node->id, 10, 20, $start + 10);
        $this->log($disabled->id, $node->id, 30, 40, $start + 20);
        $this->log($banned->id, $node->id, 50, 60, $start + 30);

        Artisan::call('task:hourly');

        // 用户表：活跃用户得到「每节点行 + 合计行」，非活跃用户一行都没有
        $this->assertSame(2, UserHourlyDataFlow::where('user_id', $active->id)->count());
        $this->assertFlow(UserHourlyDataFlow::where('user_id', $active->id)->whereNull('node_id')->first(), 10, 20);
        $this->assertSame(0, UserHourlyDataFlow::where('user_id', $disabled->id)->count());
        $this->assertSame(0, UserHourlyDataFlow::where('user_id', $banned->id)->count());

        // 节点表：合计统计全部用户，含被禁用/封禁的
        $this->assertFlow(NodeHourlyDataFlow::where('node_id', $node->id)->first(), 90, 120);
    }

    public function test_hourly_writes_exactly_one_row_per_node(): void
    {
        ['start' => $start] = $this->hourWindow();

        $nodeA = $this->node();
        $nodeB = $this->node();
        $u1 = $this->user();
        $u2 = $this->user();

        $this->log($u1->id, $nodeA->id, 1, 2, $start + 10);
        $this->log($u2->id, $nodeA->id, 3, 4, $start + 10);
        $this->log($u1->id, $nodeB->id, 5, 6, $start + 10);

        Artisan::call('task:hourly');

        $this->assertSame(1, NodeHourlyDataFlow::where('node_id', $nodeA->id)->count());
        $this->assertFlow(NodeHourlyDataFlow::where('node_id', $nodeA->id)->first(), 4, 6);
        $this->assertFlow(NodeHourlyDataFlow::where('node_id', $nodeB->id)->first(), 5, 6);
    }

    public function test_hourly_alerts_admin_when_a_user_crosses_the_abuse_threshold(): void
    {
        $admin = $this->admin();
        config(['settings.data_anomaly_notification' => ['mail'], 'settings.traffic_abuse_limit' => 10]);

        ['start' => $start] = $this->hourWindow();
        $node = $this->node();
        $user = $this->user();
        $this->log($user->id, $node->id, 5 * GiB, 6 * GiB, $start + 10); // 合计 11 GiB ≥ 10 GiB 阈值

        Artisan::call('task:hourly');

        Notification::assertSentTo($admin, DataAnomaly::class);
    }

    public function test_hourly_backfill_with_at_rewrites_only_its_own_window(): void
    {
        $admin = $this->admin();
        config(['settings.data_anomaly_notification' => ['mail'], 'settings.traffic_abuse_limit' => 10]);

        $node = $this->node();
        $user = $this->user();
        $at = '2026-03-04 05:00';
        $createdAt = '2026-03-04 05:59:59';
        $other = '2026-03-04 04:59:59';

        $this->log($user->id, $node->id, 5 * GiB, 6 * GiB, strtotime('2026-03-04 05:30:00')); // 11 GiB ≥ 10 GiB 阈值
        UserHourlyDataFlow::insert([
            ['user_id' => $user->id, 'node_id' => null, 'u' => 1, 'd' => 2, 'created_at' => $other],
        ]);

        Artisan::call('task:hourly', ['--at' => $at]);
        Artisan::call('task:hourly', ['--at' => $at]); // 同一窗口重跑不得翻倍

        $this->assertSame(2, UserHourlyDataFlow::where('created_at', $createdAt)->where('user_id', $user->id)->count());
        $this->assertSame(1, NodeHourlyDataFlow::where('created_at', $createdAt)->where('node_id', $node->id)->count());
        $this->assertFlow(UserHourlyDataFlow::where('created_at', $createdAt)->where('user_id', $user->id)->whereNull('node_id')->first(), 5 * GiB, 6 * GiB);
        $this->assertSame(1, UserHourlyDataFlow::where('created_at', $other)->count()); // 别的窗口不受影响
        Notification::assertNotSentTo($admin, DataAnomaly::class); // 回填不重发当小时告警
    }

    public function test_hourly_backfill_of_a_log_purged_window_keeps_existing_rows(): void
    {
        // 模拟流量日志已超保留期被清、hourly 行还在：回填这个无日志的窗口不能把已有统计 delete 掉
        $user = $this->user();
        $createdAt = '2020-01-01 05:59:59';
        UserHourlyDataFlow::insert([
            ['user_id' => $user->id, 'node_id' => null, 'u' => 111, 'd' => 222, 'created_at' => $createdAt],
        ]);

        Artisan::call('task:hourly', ['--at' => '2020-01-01 05:00']); // 窗口内没有任何 user_traffic_log

        $this->assertFlow(UserHourlyDataFlow::where('user_id', $user->id)->where('created_at', $createdAt)->whereNull('node_id')->first(), 111, 222);
    }

    public function test_daily_writes_user_and_node_rows_for_yesterday(): void
    {
        ['start' => $start, 'end' => $end, 'created_at' => $createdAt] = $this->dayWindow();

        $node = $this->node();
        $active = $this->user(['status' => 1, 'enable' => 1]);
        $disabled = $this->user(['status' => 1, 'enable' => 0]);

        $this->log($active->id, $node->id, 1000, 2000, $start + 100);
        $this->log($active->id, $node->id, 500, 600, $end - 100);
        $this->log($disabled->id, $node->id, 70, 80, $start + 200);

        Artisan::call('task:daily');

        $this->assertFlow(UserDailyDataFlow::where('user_id', $active->id)->where('node_id', $node->id)->first(), 1500, 2600);
        $total = UserDailyDataFlow::where('user_id', $active->id)->whereNull('node_id')->first();
        $this->assertFlow($total, 1500, 2600);
        $this->assertSame($createdAt, (string) $total->created_at);
        $this->assertSame(0, UserDailyDataFlow::where('user_id', $disabled->id)->count());

        // 节点行统计全部用户（含被禁用的）；全站合计行还会并入库里其它节点的当日流量，故只断下界
        $this->assertFlow(NodeDailyDataFlow::where('node_id', $node->id)->first(), 1570, 2680);
        $this->assertGreaterThanOrEqual(1570, (int) NodeDailyDataFlow::whereNull('node_id')->where('created_at', $createdAt)->sum('u'));
    }

    public function test_daily_rerun_does_not_double_count(): void
    {
        ['start' => $start, 'created_at' => $createdAt] = $this->dayWindow();

        $node = $this->node();
        $user = $this->user();
        $this->log($user->id, $node->id, 1000, 2000, $start + 100);

        Artisan::call('task:daily');
        Artisan::call('task:daily'); // 重跑同一天：delete+insert 原子替换，不得翻倍

        // 用户表：每节点行 + 合计行 = 2（不是 4）
        $this->assertSame(2, UserDailyDataFlow::where('user_id', $user->id)->count());
        $this->assertFlow(UserDailyDataFlow::where('user_id', $user->id)->whereNull('node_id')->first(), 1000, 2000);
        // 节点表：该节点当日 1 行；全站合计（node_id=null）也只 1 行，未被重复插入
        $this->assertSame(1, NodeDailyDataFlow::where('node_id', $node->id)->where('created_at', $createdAt)->count());
        $this->assertSame(1, NodeDailyDataFlow::whereNull('node_id')->where('created_at', $createdAt)->count());
    }

    public function test_users_are_selected_by_window_range_rather_than_by_probing_each_user(): void
    {
        foreach (['task:hourly' => $this->hourWindow(), 'task:daily' => $this->dayWindow()] as $command => $window) {
            $node = $this->node();
            $user = $this->user();
            $this->log($user->id, $node->id, 10, 20, $window['start'] + 10);

            $queries = $this->ranQueries($command);

            $this->assertFalse($this->hasPerUserProbe($queries), $command.' 仍在按账号逐个探测 user_traffic_log');
            // 反向对照：判据必须认得出旧形状，否则上面那条断言是空过
            $this->assertTrue($this->hasPerUserProbe(['select * from `user` where exists (select * from `user_traffic_log` where `user`.`id` = `user_traffic_log`.`user_id`)']));
            // 取 uid 走的是一条 log_time 区间扫描：读多少行由窗口决定，不由本表体积决定
            $this->assertNotEmpty(array_filter($queries, fn ($sql) => str_contains($sql, 'from `user_traffic_log` where `log_time` between')), $command.' 没有按区间取窗口内的上报');
        }
    }

    public function test_daily_reads_the_raw_log_in_hour_windows_rather_than_one_day_scan(): void
    {
        ['start' => $start] = $this->dayWindow();

        $node = $this->node();
        $user = $this->user();
        $this->log($user->id, $node->id, 10, 20, $start + 10);
        $this->log($user->id, $node->id, 1, 2, $start + Day - Hour); // 落在另一个小时切片里

        $spans = $this->groupedWindowSpans($this->ranQueriesWithBindings('task:daily'));

        $this->assertCount(24, $spans, 'task:daily 的原始日志分组读取不是 24 个小时切片');
        $this->assertLessThan(Hour, max($spans), 'task:daily 仍有整日窗口的分组查询：它会放弃 log_time 的区间扫描');

        // 反向对照：判据必须认得出「一条整日扫描」那种形状，否则上面两条断言是空过
        $bad = [['query' => 'select `user_id`, `node_id`, SUM(u) as u, SUM(d) as d from `user_traffic_log` '
            .'where `log_time` between ? and ? group by `user_id`, `node_id`', 'bindings' => [1000, 1000 + Day - 1]]];
        $this->assertSame([Day - 1], $this->groupedWindowSpans($bad));

        // 切片不能改变合并结果：同一 (账号,节点) 落在两个切片里的上报仍要折成一行
        $this->assertFlow(UserDailyDataFlow::where('user_id', $user->id)->where('node_id', $node->id)->first(), 11, 22);
        $this->assertFlow(UserDailyDataFlow::where('user_id', $user->id)->whereNull('node_id')->first(), 11, 22);
    }

    public function test_hourly_ignores_users_whose_only_reports_fall_outside_the_window(): void
    {
        ['start' => $start] = $this->hourWindow();

        $node = $this->node();
        $inside = $this->user();
        $outside = $this->user();

        $this->log($inside->id, $node->id, 10, 20, $start + 10);
        $this->log($outside->id, $node->id, 700, 800, $start - 10);

        Artisan::call('task:hourly');

        $this->assertSame(0, UserHourlyDataFlow::where('user_id', $outside->id)->count());
        $this->assertFlow(UserHourlyDataFlow::where('user_id', $inside->id)->whereNull('node_id')->first(), 10, 20);
    }

    public function test_daily_succeeds_and_keeps_existing_rows_when_yesterday_has_no_logs(): void
    {
        // 与 test_daily_* 同一前提：本机库里昨日窗口没有真实上报
        ['created_at' => $createdAt] = $this->dayWindow();

        $user = $this->user();
        NodeDailyDataFlow::insert(['node_id' => null, 'u' => 111, 'd' => 222, 'created_at' => $createdAt]);
        UserDailyDataFlow::insert(['user_id' => $user->id, 'node_id' => null, 'u' => 33, 'd' => 44, 'created_at' => $createdAt]);

        // 空窗也要正常退出：不带 GROUP BY 的 SUM 在空集上返回 NULL，会撞 node_daily_data_flow.u 的 NOT NULL（1048）并连带整段回滚
        $this->assertSame(0, Artisan::call('task:daily'));

        $this->assertSame(1, NodeDailyDataFlow::where('created_at', $createdAt)->whereNull('node_id')->count());
        $this->assertFlow(NodeDailyDataFlow::where('created_at', $createdAt)->whereNull('node_id')->first(), 111, 222);
        $this->assertFlow(UserDailyDataFlow::where('created_at', $createdAt)->where('user_id', $user->id)->first(), 33, 44);
    }

    public function test_traffic_statistics_aggregate_before_the_replacement_transaction_opens(): void
    {
        // 插一行就经外键给 user / node 父行加一把 S 锁并持有到提交：聚合还留在事务里，锁就要按住整段重建
        $windows = ['task:hourly' => ['hourly', $this->hourWindow()], 'task:daily' => ['daily', $this->dayWindow()]];
        $node = $this->node();

        foreach ($windows as $command => [$suffix, $window]) {
            $user = $this->user();
            $this->log($user->id, $node->id, 10, 20, $window['start'] + 10);
        }

        foreach ($windows as $command => [$suffix, $window]) {
            $queries = $this->ranQueries($command);
            $reads = array_filter($queries, fn (string $sql): bool => str_contains($sql, 'from `user_traffic_log`'));
            $writes = array_filter($queries, fn (string $sql): bool => str_contains($sql, $suffix.'_data_flow`')
                && (str_contains($sql, 'delete from `') || str_contains($sql, 'insert into `')));

            $this->assertNotEmpty($reads, $command.' 没取到聚合查询');
            $this->assertNotEmpty($writes, $command.' 没取到统计表的删插语句');
            $this->assertLessThan(
                array_key_first($writes),
                array_key_last($reads),
                $command.' 的聚合排在删插之后（还在事务里），外键 S 锁会被按住整段重建'
            );
        }

        // 反向对照：同一对判据用在「读排在写之后」的坏形状上，方向必须反过来成立，否则上面的断言是空转
        $bad = ['delete from `user_daily_data_flow` where `created_at` = ?', 'select * from `user_traffic_log` where `log_time` between ? and ?'];
        $this->assertGreaterThan(
            array_key_first(array_filter($bad, fn (string $sql): bool => str_contains($sql, 'delete from `user_daily_data_flow`'))),
            array_key_last(array_filter($bad, fn (string $sql): bool => str_contains($sql, 'from `user_traffic_log`')))
        );
    }

    /** 命令实际发出的 SQL 列表 */
    private function ranQueries(string $command): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        Artisan::call($command);
        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        return $queries;
    }

    /** 命令实际发出的 SQL 与绑定值 */
    private function ranQueriesWithBindings(string $command): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        Artisan::call($command);
        $logged = DB::getQueryLog();
        DB::disableQueryLog();

        return $logged;
    }

    /**
     * 每条「按窗口分组读原始日志」的窗口跨度（秒）：这类查询的绑定值就只有区间两端.
     *
     * @param  array<int, array{query: string, bindings: array<int, mixed>}>  $logged
     * @return array<int, int>
     */
    private function groupedWindowSpans(array $logged): array
    {
        $spans = [];
        foreach ($logged as $entry) {
            if (! str_contains($entry['query'], 'from `user_traffic_log`') || ! str_contains($entry['query'], 'group by')) {
                continue;
            }

            $window = array_values(array_filter($entry['bindings'], fn ($value) => is_int($value)));
            self::assertCount(2, $window, '分组读取的绑定值不再是区间两端，判据取不到窗口：'.implode(',', $window));
            $spans[] = max($window) - min($window);
        }

        return $spans;
    }

    /** 「对每个账号做一次 exists(user_traffic_log)」的形状：外层按 id 关联、log_time 只能逐行过滤 */
    private function hasPerUserProbe(array $queries): bool
    {
        return (bool) collect($queries)->first(fn ($sql) => preg_match('/exists\s*\(\s*select\s+.*?from\s+`user_traffic_log`\s+where\s+`user`\.`id`/is', $sql));
    }

    private function hourWindow(): array
    {
        $createdAt = date('Y-m-d H:59:59', strtotime('-1 hour'));
        $end = strtotime($createdAt);

        return ['start' => $end - 3599, 'end' => $end, 'created_at' => $createdAt];
    }

    private function dayWindow(): array
    {
        $createdAt = date('Y-m-d 23:59:59', strtotime('yesterday'));
        $end = strtotime($createdAt);

        return ['start' => $end - 86399, 'end' => $end, 'created_at' => $createdAt];
    }

    private function node(): Node
    {
        return Node::withoutEvents(fn () => Node::create([
            'name' => 'stat-node-'.bin2hex(random_bytes(6)),
            'profile' => [],
        ]));
    }

    private function user(array $attrs = []): User
    {
        return User::withoutEvents(fn () => User::create(array_merge([
            'username' => 'stat-'.bin2hex(random_bytes(8)).'@example.com',
            'nickname' => 'stat-test',
            'passwd' => 'pw',
            'status' => 1,
            'enable' => 1,
        ], $attrs)));
    }

    private function log(int $userId, int $nodeId, int $u, int $d, int $logTime): void
    {
        UserDataFlowLog::create([
            'user_id' => $userId,
            'node_id' => $nodeId,
            'u' => $u,
            'd' => $d,
            'rate' => 1,
            'log_time' => $logTime,
        ]);
    }

    private function assertFlow(?Model $row, int $u, int $d): void
    {
        $this->assertNotNull($row, '期望的流量统计行缺失');
        $this->assertSame($u, (int) $row->u);
        $this->assertSame($d, (int) $row->d);
    }
}
