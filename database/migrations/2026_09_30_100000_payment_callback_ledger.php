<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 留档发生在验签之前，这些列必须可空并带默认值；改列用裸 SQL（->change() 需要 doctrine/dbal）
        DB::statement("ALTER TABLE `payment_callback`
            ADD `method` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '支付方式' AFTER `id`,
            MODIFY `trade_no` VARCHAR(64) NULL DEFAULT NULL COMMENT '本地支付单号（payment.trade_no）',
            MODIFY `out_trade_no` VARCHAR(64) NULL DEFAULT NULL COMMENT '支付平台交易号',
            ADD `payload` TEXT NULL COMMENT '回调报文，已脱敏' AFTER `out_trade_no`,
            ADD `ip` VARCHAR(45) NULL COMMENT '回调来源IP' AFTER `payload`,
            MODIFY `amount` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '交易金额，单位分',
            MODIFY `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '履约状态：0-未履约、1-已履约'");

        // 列表按单号查、清理任务按 created_at 删，两列都要索引
        Schema::table('payment_callback', static function (Blueprint $table) {
            $table->index('trade_no', 'idx_payment_callback_trade_no');
            $table->index('created_at', 'idx_payment_callback_created_at');
        });
    }

    public function down(): void
    {
        // 只回滚新增列与索引：NULL 值改不回 NOT NULL
        Schema::table('payment_callback', static function (Blueprint $table) {
            $table->dropIndex('idx_payment_callback_trade_no');
            $table->dropIndex('idx_payment_callback_created_at');
            $table->dropColumn(['method', 'payload', 'ip']);
        });
    }
};
