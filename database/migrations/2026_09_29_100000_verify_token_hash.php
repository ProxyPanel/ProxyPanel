<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 库里存 sha256（64 位十六进制），varchar(32) 装不下；->change() 需要 doctrine/dbal，故用裸 SQL
        DB::statement("ALTER TABLE `verify` MODIFY `token` VARCHAR(64) NOT NULL COMMENT '校验token的sha256哈希'");

        // 激活与改密都按 token 精确查一条，user_id 索引覆盖不到这个查询
        Schema::table('verify', static function (Blueprint $table) {
            $table->index('token', 'idx_verify_token');
        });
    }

    public function down(): void
    {
        Schema::table('verify', static function (Blueprint $table) {
            $table->dropIndex('idx_verify_token');
        });

        DB::statement("ALTER TABLE `verify` MODIFY `token` VARCHAR(32) NOT NULL COMMENT '校验token'");
    }
};
