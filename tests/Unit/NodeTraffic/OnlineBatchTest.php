<?php

namespace Tests\Unit\NodeTraffic;

use App\Utils\NodeTraffic\OnlineBatch;
use App\Utils\NodeTraffic\TrafficBatch;
use PHPUnit\Framework\TestCase;

/**
 * 这里继承裸 PHPUnit 的 TestCase：OnlineBatch 是纯判定，不碰数据库也不启动容器。
 *
 * 钉住的判据是「失效账号只跳自己、不连累同批其他用户」——node_online_ip.user_id 有外键，
 * 失效行必须留在 missing 里而不是被写库，但整批拒收会把这一分钟的在线记录一起丢掉。
 * 另一条是行序：在线 INSERT 与流量 UPDATE 必须按 uid 升序取同一批 user 行锁，否则并发上报会互锁成环。
 */
class OnlineBatchTest extends TestCase
{
    public function test_all_rows_valid(): void
    {
        $result = OnlineBatch::build(
            [['uid' => 11, 'ip' => '1.2.3.4'], ['uid' => 12, 'ip' => '5.6.7.8']],
            [11 => 10011, 12 => 10012],
            7,
            1700000000,
        );

        $this->assertSame([
            ['node_id' => 7, 'user_id' => 11, 'ip' => '1.2.3.4', 'port' => 10011, 'created_at' => 1700000000],
            ['node_id' => 7, 'user_id' => 12, 'ip' => '5.6.7.8', 'port' => 10012, 'created_at' => 1700000000],
        ], $result['rows']);
        $this->assertSame([], $result['missing']);
    }

    public function test_stale_account_is_skipped_without_dropping_the_batch(): void
    {
        $result = OnlineBatch::build(
            [['uid' => 11, 'ip' => '1.2.3.4'], ['uid' => 99, 'ip' => '5.6.7.8'], ['uid' => 12, 'ip' => '9.9.9.9']],
            [11 => 10011, 12 => 10012],
            7,
            1700000000,
        );

        $this->assertCount(2, $result['rows']);
        $this->assertSame([11, 12], array_column($result['rows'], 'user_id'));
        $this->assertSame([99], $result['missing']);
    }

    public function test_repeated_stale_account_is_reported_once(): void
    {
        $result = OnlineBatch::build(
            [['uid' => 99, 'ip' => '1.1.1.1'], ['uid' => 99, 'ip' => '2.2.2.2']],
            [],
            7,
            1700000000,
        );

        $this->assertSame([], $result['rows']);
        $this->assertSame([99], $result['missing']);
    }

    public function test_string_ids_and_ports_from_the_driver_are_normalised(): void
    {
        $result = OnlineBatch::build([['uid' => '11', 'ip' => '1.2.3.4']], ['11' => '10011'], 7, 1700000000);

        $this->assertSame([
            ['node_id' => 7, 'user_id' => 11, 'ip' => '1.2.3.4', 'port' => 10011, 'created_at' => 1700000000],
        ], $result['rows']);
    }

    public function test_multi_ip_report_is_kept_as_one_row(): void
    {
        // 节点把同一用户这一轮连过的多个出口用逗号拼在一个字段里（node_online_ip.ip 的列注释就是这么定的）
        $result = OnlineBatch::build([['uid' => 11, 'ip' => '1.2.3.4,5.6.7.8']], [11 => 10011], 7, 1700000000);

        $this->assertSame('1.2.3.4,5.6.7.8', $result['rows'][0]['ip']);
        $this->assertCount(1, $result['rows']);
    }

    public function test_empty_report_builds_nothing(): void
    {
        $result = OnlineBatch::build([], [], 7, 1700000000);

        $this->assertSame([], $result['rows']);
        $this->assertSame([], $result['missing']);
    }

    public function test_rows_are_ordered_by_uid_for_the_shared_lock_order(): void
    {
        $records = [
            ['uid' => 30, 'ip' => '1.1.1.1'],
            ['uid' => 5, 'ip' => '2.2.2.2'],
            ['uid' => 17, 'ip' => '3.3.3.3'],
            ['uid' => 5, 'ip' => '4.4.4.4'],
        ];

        $result = OnlineBatch::build($records, [5 => 10005, 17 => 10017, 30 => 10030], 7, 1700000000);

        $this->assertSame([5, 5, 17, 30], array_column($result['rows'], 'user_id'));
        // 与流量上报路径同一把锁序：aggregate() 会把同 uid 合成一行，所以比的是去重后的先后次序
        $this->assertSame(
            array_keys(TrafficBatch::aggregate(
                array_map(fn (array $record): array => $record + ['upload' => 1, 'download' => 1], $records),
                1.0,
            )),
            array_values(array_unique(array_column($result['rows'], 'user_id'))),
        );
    }

    public function test_same_uid_rows_keep_their_report_order(): void
    {
        // 同一用户被拆成多行时（多出口分行上报），排序只按 uid 比较，行间相对顺序保持不变
        $result = OnlineBatch::build(
            [
                ['uid' => 9, 'ip' => '1.1.1.1'],
                ['uid' => 3, 'ip' => '2.2.2.2'],
                ['uid' => 9, 'ip' => '3.3.3.3'],
                ['uid' => 9, 'ip' => '4.4.4.4'],
            ],
            [3 => 10003, 9 => 10009],
            7,
            1700000000,
        );

        $this->assertSame([3, 9, 9, 9], array_column($result['rows'], 'user_id'));
        $this->assertSame(['2.2.2.2', '1.1.1.1', '3.3.3.3', '4.4.4.4'], array_column($result['rows'], 'ip'));
        $this->assertSame([0, 1, 2, 3], array_keys($result['rows']));
    }
}
