<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 给定时任务里「每分钟 / 每 5 分钟」跑一次的清理与过期判定补索引。
 *
 * 这些语句此前全部是「status 等值 + 时间列区间」的形状，而相关表要么只有主键、
 * 要么索引的最左列不是 status，于是每分钟都在做全表扫描 + 全量更新。
 */
return new class extends Migration
{
    public function up(): void
    {
        // 索引名是否已存在按安装 lineage 而定，迁移中途失败重放时也已建过一部分，所以建与删都先判。
        // TaskAuto::unblockSubscribes：where status = 0 and ban_time <= now()
        // 原有的 user_id(user_id, status) 最左列是 user_id，用不上；被封禁的订阅是极少数，
        // 走这个索引后是紧凑区间扫描。
        if (! Schema::hasIndex('user_subscribe', 'idx_user_subscribe_ban')) {
            Schema::table('user_subscribe', static function (Blueprint $table) {
                $table->index(['status', 'ban_time'], 'idx_user_subscribe_ban');
            });
        }

        // TaskAuto::expireCode：VerifyCode::recentUnused() = where status = 0 and created_at <= ?
        // verify_code 此前只有主键，且全仓没有任何删除它的地方，注册验证码会一直累积。
        if (! Schema::hasIndex('verify_code', 'idx_verify_code_status_created')) {
            Schema::table('verify_code', static function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'idx_verify_code_status_created');
            });
        }

        // TaskAuto::expireCode：where status <> 2 and end_time <= ?
        // coupon 此前只有主键。status 在前：未失效的券是少数，时间列只作区间收尾。
        if (! Schema::hasIndex('coupon', 'idx_coupon_status_end')) {
            Schema::table('coupon', static function (Blueprint $table) {
                $table->index(['status', 'end_time'], 'idx_coupon_status_end');
            });
        }

        // TaskAuto::expireCode：where status = 0 and dateline <= ?
        if (! Schema::hasIndex('invite', 'idx_invite_status_dateline')) {
            Schema::table('invite', static function (Blueprint $table) {
                $table->index(['status', 'dateline'], 'idx_invite_status_dateline');
            });
        }

        // ServiceTimer::expiredPlan（每 5 分钟）：Order::activePlan() 即
        // where is_expire = 0 and status = 2 and expired_at <= ?
        // 既有的 idx_order_status_created(status, created_at) 第二列是 created_at，顶不住 expired_at 的区间，
        // 于是每 5 分钟扫一遍全部「已完成」订单。
        if (! Schema::hasIndex('order', 'idx_order_expired_plan')) {
            Schema::table('order', static function (Blueprint $table) {
                $table->index(['status', 'is_expire', 'expired_at'], 'idx_order_expired_plan');
            });
        }

        // TaskAuto::blockSubscribes（每 5 分钟一次的相关子查询）：
        // select count(*) from user_subscribe_log
        //   inner join user_subscribe on user_subscribe.id = user_subscribe_log.user_subscribe_id
        //   where user.id = user_subscribe.user_id and request_time >= ?
        // 按订阅号等值 + 时间区间取数，所以需要 (user_subscribe_id, request_time)。
        if (! Schema::hasIndex('user_subscribe_log', 'idx_subscribe_log_sub_time')) {
            Schema::table('user_subscribe_log', static function (Blueprint $table) {
                $table->index(['user_subscribe_id', 'request_time'], 'idx_subscribe_log_sub_time');
            });
        }

        // 上面的复合索引最左列就是外键列，原外键索引 user_subscribe_log_user_subscribe_id_index
        // 成了它的前缀副本，可以去掉（旧表是高频写入表，少维护一棵 B-tree）。
        // 顺序不能颠倒：先建新的再删旧的，否则 MySQL 会以 errno 1553 拒绝。
        if (Schema::hasIndex('user_subscribe_log', 'user_subscribe_log_user_subscribe_id_index')) {
            Schema::table('user_subscribe_log', static function (Blueprint $table) {
                $table->dropIndex('user_subscribe_log_user_subscribe_id_index');
            });
        }
    }

    public function down(): void
    {
        // 同样先建后删，保证外键始终有可用的索引
        if (! Schema::hasIndex('user_subscribe_log', 'user_subscribe_log_user_subscribe_id_index')) {
            Schema::table('user_subscribe_log', static function (Blueprint $table) {
                $table->index('user_subscribe_id', 'user_subscribe_log_user_subscribe_id_index');
            });
        }

        if (Schema::hasIndex('user_subscribe_log', 'idx_subscribe_log_sub_time')) {
            Schema::table('user_subscribe_log', static function (Blueprint $table) {
                $table->dropIndex('idx_subscribe_log_sub_time');
            });
        }

        if (Schema::hasIndex('order', 'idx_order_expired_plan')) {
            Schema::table('order', static function (Blueprint $table) {
                $table->dropIndex('idx_order_expired_plan');
            });
        }

        if (Schema::hasIndex('invite', 'idx_invite_status_dateline')) {
            Schema::table('invite', static function (Blueprint $table) {
                $table->dropIndex('idx_invite_status_dateline');
            });
        }

        if (Schema::hasIndex('coupon', 'idx_coupon_status_end')) {
            Schema::table('coupon', static function (Blueprint $table) {
                $table->dropIndex('idx_coupon_status_end');
            });
        }

        if (Schema::hasIndex('verify_code', 'idx_verify_code_status_created')) {
            Schema::table('verify_code', static function (Blueprint $table) {
                $table->dropIndex('idx_verify_code_status_created');
            });
        }

        if (Schema::hasIndex('user_subscribe', 'idx_user_subscribe_ban')) {
            Schema::table('user_subscribe', static function (Blueprint $table) {
                $table->dropIndex('idx_user_subscribe_ban');
            });
        }
    }
};
