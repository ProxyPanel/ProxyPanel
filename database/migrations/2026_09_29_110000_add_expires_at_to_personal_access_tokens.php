<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sanctum 早期建表迁移装出来的库没有这一列，已有库不会重放建表迁移，只能在这里补
        if (! Schema::hasColumn('personal_access_tokens', 'expires_at')) {
            Schema::table('personal_access_tokens', static function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('abilities');
            });
        }
    }

    public function down(): void
    {
        // 不删列：分不清是本迁移加的还是安装时就有
    }
};
