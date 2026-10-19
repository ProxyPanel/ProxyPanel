<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'user_traffic_log';

    private const COLUMN = 'traffic';

    /**
     * traffic 存的是 formatBytes(u + d) 那个字符串，同一行里 u 与 d 已经是真值，它是纯冗余展示字段，
     * 却要跟着这台库每秒都在长的那张表一起膨胀（实测单行两个 bigint 之外还挂 20 字节左右的 varchar）。
     *
     * **部署顺序是硬约束**：必须等队列 worker 与 php-fpm 都跑到「不再往 INSERT 里带 traffic」的新代码之后再执行本迁移，
     * 否则旧代码会在这一列已经不存在的表上写它，直接撞 Unknown column 把整批流量上报打失败。
     * update.sh / update-quick.sh 会把 migrate 排在 horizon:terminate 之前，所以要分两步部署，不能一把梭。
     */
    public function up(): void
    {
        if (! Schema::hasColumn(self::TABLE, self::COLUMN)) {
            return; // 全新安装由快照直接给出没有这一列的形状，重放在这里是空操作
        }

        Schema::table(self::TABLE, static function (Blueprint $table) {
            $table->dropColumn(self::COLUMN);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn(self::TABLE, self::COLUMN)) {
            return;
        }

        // 默认空串：原定义没有默认值，靠部署端空的 sql_mode 隐式补，但 ADD COLUMN 带回 NOT NULL 而不给默认值会当场报错
        Schema::table(self::TABLE, static function (Blueprint $table) {
            $table->string(self::COLUMN, 32)->default('')->comment('产生流量');
        });
    }
};
