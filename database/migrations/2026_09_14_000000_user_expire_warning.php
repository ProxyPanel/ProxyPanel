<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', static function (Blueprint $table) {
            // 到期提醒的幂等标记：记录最后一次提醒的日期，避免同一用户同一天被重复通知
            $table->date('expire_warned_at')->nullable()->comment('最后一次到期提醒的日期')->after('expired_at');

            // userExpireWarning 按 enable + expired_at 区间取数，原有的 idx_search(enable,status,port) 只能用到 enable 前缀
            $table->index(['enable', 'expired_at'], 'idx_user_expire_warning');
        });
    }

    public function down(): void
    {
        Schema::table('user', static function (Blueprint $table) {
            $table->dropIndex('idx_user_expire_warning');
            $table->dropColumn('expire_warned_at');
        });
    }
};
