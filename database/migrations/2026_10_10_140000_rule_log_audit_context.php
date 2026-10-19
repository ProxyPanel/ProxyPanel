<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 四条列在同一条 ALTER 里加，要么全成要么全无：列已在就是上次跑到这里之后才失败，不必重来
        if (Schema::hasColumn('rule_log', 'target')) {
            return;
        }

        // 审计命中的完整现场：reason 只是被命中的判定对象，去向与首包嗅出的身份各自成列
        Schema::table('rule_log', static function (Blueprint $table) {
            $table->string('target', 255)->nullable()->after('reason')->comment('连接真实去向 host:port');
            $table->string('sniff_protocol', 5)->nullable()->after('target')->comment('首包嗅探结果：http/tls');
            $table->string('sniff_host', 255)->nullable()->after('sniff_protocol')->comment('HTTP Host 头或 TLS SNI');
            $table->string('sniff_uri', 1024)->nullable()->after('sniff_host')->comment('HTTP 请求路径，tls 无');
        });
    }

    public function down(): void
    {
        Schema::table('rule_log', static function (Blueprint $table) {
            $table->dropColumn(['target', 'sniff_protocol', 'sniff_host', 'sniff_uri']);
        });
    }
};
