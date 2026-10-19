<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'node_online_ip';

    private const COLUMN = 'port';

    /** 快照里这份索引的名字，rollback 按它补回 */
    private const RESTORE_NAME = 'node_online_ip_port_index';

    /**
     * port 上的二级索引没有任何读取方：后台四处按 port 筛都落在 user 表上（whereHas('user') 或 User 查询本身），
     * 这张表自己只在 select 列表里带上它。而它是这台库最高频写入表上维护的四棵二级树之一，白烧插入成本。
     *
     * 名字取不得：同列索引在生产与快照里未必同名，所以判据取「只覆盖 port 这一列」。
     */
    public function up(): void
    {
        foreach (self::portIndexNames(Schema::getIndexes(self::TABLE)) as $name) {
            Schema::table(self::TABLE, static function (Blueprint $table) use ($name) {
                $table->dropIndex($name);
            });
        }
    }

    public function down(): void
    {
        if (self::portIndexNames(Schema::getIndexes(self::TABLE)) !== []) {
            return; // 同列已经有索引，不补第二条
        }

        Schema::table(self::TABLE, static function (Blueprint $table) {
            $table->index(self::COLUMN, self::RESTORE_NAME);
        });
    }

    /**
     * 只覆盖 COLUMN 这一列的索引名（多列索引不算，它有别的用途）.
     *
     * @param  array<int, array{name: string, columns: array<int, string>, unique: bool, primary: bool}>  $indexes  Schema::getIndexes() 的整表结果
     * @return array<int, string>
     */
    private static function portIndexNames(array $indexes): array
    {
        return array_column(array_filter(
            $indexes,
            static fn (array $index) => $index['columns'] === [self::COLUMN]
        ), 'name');
    }
};
