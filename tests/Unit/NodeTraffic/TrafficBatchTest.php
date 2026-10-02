<?php

namespace Tests\Unit\NodeTraffic;

use App\Utils\NodeTraffic\TrafficBatch;
use PHPUnit\Framework\TestCase;

/**
 * 与 OnlineReportTest 同理继承裸 PHPUnit 的 TestCase：本项目启动 Laravel 容器需要可达的数据库，
 * 而这里的聚合与 SQL 拼装不依赖数据库，能真正跑起来。
 *
 * 重点锁两件事：绑定值与占位符必须一一对应（手写 CASE 批量更新最容易错的地方），
 * 以及切块时必须保留用户ID 作为键（否则 UPDATE 会张冠李戴）。
 */
class TrafficBatchTest extends TestCase
{
    public function test_aggregate_applies_rate_and_merges_duplicate_uids(): void
    {
        $records = [
            ['uid' => 5, 'upload' => 1000, 'download' => 2000],
            ['uid' => 7, 'upload' => 300, 'download' => '400'],
            ['uid' => 5, 'upload' => '500', 'download' => 600],
        ];

        $this->assertSame([5 => ['u' => 1500, 'd' => 2600], 7 => ['u' => 300, 'd' => 400]], TrafficBatch::aggregate($records, 1.0));
        $this->assertSame([5 => ['u' => 750, 'd' => 1300], 7 => ['u' => 150, 'd' => 200]], TrafficBatch::aggregate($records, 0.5));
    }

    public function test_aggregate_never_lets_traffic_go_backwards(): void
    {
        $records = [
            ['uid' => 5, 'upload' => -1000, 'download' => -2000],
            ['uid' => 5, 'upload' => 100, 'download' => 200],
        ];

        // 负增量归零，只有正的部分生效（user.u/d 是 unsigned，写负值在严格模式下会直接报错）
        $this->assertSame([5 => ['u' => 100, 'd' => 200]], TrafficBatch::aggregate($records, 1.0));
    }

    public function test_build_log_rows_carries_node_and_formatted_traffic(): void
    {
        $rows = TrafficBatch::buildLogRows([5 => ['u' => MiB, 'd' => MiB]], 9, 1.5, 1700000000);

        $this->assertSame([[
            'user_id' => 5,
            'node_id' => 9,
            'u' => MiB,
            'd' => MiB,
            'rate' => 1.5,
            'traffic' => formatBytes(2 * MiB),
            'log_time' => 1700000000,
        ]], $rows);
    }

    public function test_update_query_binds_every_placeholder(): void
    {
        $traffic = [5 => ['u' => 1500, 'd' => 2600], 7 => ['u' => 300, 'd' => 400]];
        $query = TrafficBatch::buildUpdateQuery($traffic, 1700000000);

        $this->assertSame(
            'UPDATE user SET u = u + CASE id WHEN ? THEN ? WHEN ? THEN ? ELSE 0 END, d = d + CASE id WHEN ? THEN ? WHEN ? THEN ? ELSE 0 END, t = ? WHERE id IN (?,?)',
            $query['sql']
        );

        // 顺序：u 的 CASE 对、d 的 CASE 对、t、WHERE 的 id 列表 —— 必须与占位符出现顺序一致
        $this->assertSame([5, 1500, 7, 300, 5, 2600, 7, 400, 1700000000, 5, 7], $query['bindings']);
        $this->assertSame(substr_count($query['sql'], '?'), count($query['bindings']));
    }

    public function test_update_query_for_single_user(): void
    {
        $query = TrafficBatch::buildUpdateQuery([5 => ['u' => 1, 'd' => 2]], 1700000000);

        $this->assertSame('UPDATE user SET u = u + CASE id WHEN ? THEN ? ELSE 0 END, d = d + CASE id WHEN ? THEN ? ELSE 0 END, t = ? WHERE id IN (?)', $query['sql']);
        $this->assertSame(substr_count($query['sql'], '?'), count($query['bindings']));
    }

    public function test_chunks_keep_user_id_as_key(): void
    {
        $traffic = [];
        for ($uid = 1; $uid <= TrafficBatch::CHUNK_SIZE + 1; $uid++) {
            $traffic[$uid] = ['u' => $uid, 'd' => $uid];
        }

        $chunks = TrafficBatch::chunks($traffic);

        $this->assertCount(2, $chunks);
        $this->assertSame([TrafficBatch::CHUNK_SIZE, 1], array_map('count', $chunks));

        // 键被保留成用户ID，才能直接喂给 buildUpdateQuery()：WHERE 的 id 绑定值就是这些键
        foreach ($chunks as $chunk) {
            foreach ($chunk as $uid => $row) {
                $this->assertSame($uid, $row['u'], 'u 里存的是 uid，用来核对键没有串位');
            }

            $this->assertSame(array_keys($chunk), array_slice(TrafficBatch::buildUpdateQuery($chunk, 1)['bindings'], -count($chunk)));
        }
    }
}
