<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order', static function (Blueprint $table) {
            // 订单的两处清理都是「status 等值 + created_at 区间」的形状：
            // TaskAuto 关闭超时未支付 / 未处理人工支付订单（status 0、1），
            // TaskMonthly 删除一年前的未支付订单（status -1）。
            // 原有的 idx_order_search(user_id, goods_id, is_expire, status) 把 status 放在第 4 列，
            // 按最左前缀规则用不上，这些查询此前只能全表扫描；带 status 前缀的复合索引也能
            // 直接服务只按 status 过滤的查询（如后台按 status 取用户、按 status 统计订单）。
            $table->index(['status', 'created_at'], 'idx_order_status_created');
        });
    }

    public function down(): void
    {
        Schema::table('order', static function (Blueprint $table) {
            $table->dropIndex('idx_order_status_created');
        });
    }
};
