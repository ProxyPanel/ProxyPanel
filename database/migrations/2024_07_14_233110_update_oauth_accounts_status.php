<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('user_oauth')
            ->join('user', 'user_oauth.user_id', '=', 'user.id')
            ->where('user.status', 0)
            ->whereRaw('ABS(TIMESTAMPDIFF(SECOND, user_oauth.created_at, user.created_at)) <= 5')
            ->update(['user.status' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('user_oauth')
            ->join('user', 'user_oauth.user_id', '=', 'user.id')
            ->where('user.status', 1) // 状态从1
            ->whereRaw('ABS(TIMESTAMPDIFF(SECOND, user_oauth.created_at, user.created_at)) <= 5')
            ->update(['user.status' => 0]); // 更新为0
    }
};
