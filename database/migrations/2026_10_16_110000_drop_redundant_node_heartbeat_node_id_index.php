<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'node_heartbeat';

    private const COLUMN = 'node_id';

    /** 快照里这份同列非唯一索引的名字，rollback 按它补回 */
    private const RESTORE_NAME = 'node_heartbeat_node_id_index';

    /**
     * node_id 上的唯一索引同时是心跳 upsert 的去重键、外键的取数索引与 latestHeartbeat 的扫描路径，
     * 同列那份非唯一索引没有任何它顶不了的读取方。非唯一那份在生产叫 idx_node_id、在快照叫
     * node_heartbeat_node_id_index，所以判据取「同列 + 非唯一 + 同列存在唯一性约束」，不取名字。
     */
    public function up(): void
    {
        foreach (self::redundantNames(Schema::getIndexes(self::TABLE)) as $name) {
            Schema::table(self::TABLE, static function (Blueprint $table) use ($name) {
                $table->dropIndex($name);
            });
        }
    }

    public function down(): void
    {
        $nonUnique = array_filter(
            self::onColumn(Schema::getIndexes(self::TABLE)),
            static fn (array $index) => ! $index['unique'] && ! $index['primary']
        );

        if ($nonUnique !== []) {
            return; // 同列已经有非唯一索引，不补第二条
        }

        Schema::table(self::TABLE, static function (Blueprint $table) {
            $table->index(self::COLUMN, self::RESTORE_NAME);
        });
    }

    /**
     * 交回可以删掉的同列非唯一索引名：同列没有唯一性约束时返回空，那时它是外键唯一的取数路径.
     *
     * @param  array<int, array{name: string, columns: array<int, string>, unique: bool, primary: bool}>  $indexes  Schema::getIndexes() 的整表结果
     * @return array<int, string>
     */
    public static function redundantNames(array $indexes): array
    {
        $same = self::onColumn($indexes);
        $constrained = false;

        foreach ($same as $index) {
            if ($index['unique'] || $index['primary']) {
                $constrained = true;

                break;
            }
        }

        if (! $constrained) {
            return [];
        }

        return array_column(array_filter(
            $same,
            static fn (array $index) => ! $index['unique'] && ! $index['primary']
        ), 'name');
    }

    /**
     * 只覆盖 COLUMN 这一列的索引（多列索引与主键 id 都不算，它们各自有独立的用途）.
     *
     * @param  array<int, array{name: string, columns: array<int, string>, unique: bool, primary: bool}>  $indexes
     * @return array<int, array{name: string, columns: array<int, string>, unique: bool, primary: bool}>
     */
    private static function onColumn(array $indexes): array
    {
        return array_values(array_filter(
            $indexes,
            static fn (array $index) => $index['columns'] === [self::COLUMN]
        ));
    }
};
