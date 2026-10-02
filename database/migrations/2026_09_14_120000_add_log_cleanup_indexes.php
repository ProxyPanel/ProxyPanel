<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AutoClearLogs 每次清理都按「时间列 <= 截止时间」删除，而这 10 张表的过滤列此前全部没有索引，
     * 每 30 分钟一次的全表扫描 + 无上限 DELETE 正是 20:30 负载尖峰的来源之一。
     *
     * 表名 => [过滤列, 索引名]。user_traffic_log 的 idx_user_node_time 虽然含 log_time，但它排在第 3 列，
     * 按最左前缀规则无法用于 log_time 的区间扫描，所以这里补的是独立的单列索引。
     */
    private const CLEANUP_INDEXES = [
        'node_daily_data_flow' => ['created_at', 'idx_node_daily_created'],
        'node_hourly_data_flow' => ['created_at', 'idx_node_hourly_created'],
        'node_heartbeat' => ['log_time', 'idx_node_heartbeat_time'],
        'node_online_log' => ['log_time', 'idx_node_online_log_time'],
        'rule_log' => ['created_at', 'idx_rule_log_created'],
        'node_online_ip' => ['created_at', 'idx_node_online_ip_created'],
        'user_daily_data_flow' => ['created_at', 'idx_user_daily_created'],
        'user_hourly_data_flow' => ['created_at', 'idx_user_hourly_created'],
        'user_subscribe_log' => ['request_time', 'idx_user_subscribe_log_time'],
        'user_traffic_log' => ['log_time', 'idx_user_traffic_log_time'],
    ];

    public function up(): void
    {
        foreach (self::CLEANUP_INDEXES as $table => [$column, $index]) {
            Schema::table($table, static function (Blueprint $table) use ($column, $index) {
                $table->index($column, $index);
            });
        }
    }

    public function down(): void
    {
        foreach (self::CLEANUP_INDEXES as $table => [, $index]) {
            Schema::table($table, static function (Blueprint $table) use ($index) {
                $table->dropIndex($index);
            });
        }
    }
};
