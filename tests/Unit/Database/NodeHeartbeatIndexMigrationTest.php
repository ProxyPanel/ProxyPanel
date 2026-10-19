<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;

/**
 * 心跳表同列冗余索引的删除判据。
 *
 * 判据必须按「同列 + 非唯一 + 同列存在唯一性约束」取索引，不能按名字：同列那份非唯一索引在生产
 * 叫 idx_node_id，在 database/schema/mysql-schema.sql 里叫 node_heartbeat_node_id_index，两者要同一条迁移收掉。
 * 外键 node_heartbeat_node_id_foreign 要求 node_id 上始终有索引可用，所以唯一性约束还没建的那一侧一个都不能删。
 */
class NodeHeartbeatIndexMigrationTest extends TestCase
{
    private const MIGRATION = __DIR__.'/../../../database/migrations/2026_10_16_110000_drop_redundant_node_heartbeat_node_id_index.php';

    /**
     * @dataProvider indexShapes
     */
    public function test_it_drops_only_the_same_column_non_unique_index(array $indexes, array $expected): void
    {
        $this->assertFileExists(self::MIGRATION);
        $migration = require self::MIGRATION;

        $this->assertSame($expected, $migration::redundantNames($indexes));
    }

    public static function indexShapes(): array
    {
        $unique = self::index('node_heartbeat_node_id_unique', unique: true);
        $prodName = self::index('idx_node_id');
        $dumpName = self::index('node_heartbeat_node_id_index');
        $time = self::index('idx_node_heartbeat_time', column: 'log_time');
        $primaryKey = self::index('primary', column: 'id', unique: true, primary: true);

        return [
            '生产形状：唯一键 + idx_node_id' => [
                [$primaryKey, $unique, $prodName, $time],
                ['idx_node_id'],
            ],
            '快照形状：唯一键 + 建表时的索引名' => [
                [$primaryKey, $unique, $dumpName, $time],
                ['node_heartbeat_node_id_index'],
            ],
            '唯一键 + 两份非唯一索引' => [
                [$primaryKey, $unique, $prodName, $dumpName, $time],
                ['idx_node_id', 'node_heartbeat_node_id_index'],
            ],
            '已清理：只剩唯一键' => [
                [$primaryKey, $unique, $time],
                [],
            ],
            // 反向对照一：唯一键还没建（加唯一键那条迁移之前的形状），删掉非唯一那份会让外键没有可用索引
            '同列没有唯一性约束时一个都不删' => [
                [$primaryKey, $prodName, $time],
                [],
            ],
            // 反向对照二：主键本身就在 node_id 上时，主键既是唯一性约束也不是删除目标
            '主键落在 node_id 上时不把它当删除目标' => [
                [self::index('primary', unique: true, primary: true), $prodName, $time],
                ['idx_node_id'],
            ],
        ];
    }

    private static function index(string $name, string $column = 'node_id', bool $unique = false, bool $primary = false): array
    {
        return ['name' => $name, 'columns' => [$column], 'type' => 'btree', 'unique' => $unique, 'primary' => $primary];
    }
}
