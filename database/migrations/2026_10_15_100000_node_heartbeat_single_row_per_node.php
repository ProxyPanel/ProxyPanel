<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 心跳只被「每节点最新一条」消费（Node::latestHeartbeat 与掉线判定），历史行没有任何读取方；
     * 原先每分钟 append 一行、再由 AutoClearLogs 分批删除，是纯 churn。收敛成每节点一行并加唯一键后，
     * 写入改为原地刷新（upsert），删除自然无活可干。
     */
    public function up(): void
    {
        $keep = DB::table('node_heartbeat')->selectRaw('MAX(id) AS id')->groupBy('node_id')->pluck('id');

        DB::table('node_heartbeat')->whereNotIn('id', $keep)->delete();

        Schema::table('node_heartbeat', function (Blueprint $table) {
            $table->unique('node_id', 'node_heartbeat_node_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('node_heartbeat', function (Blueprint $table) {
            $table->dropUnique('node_heartbeat_node_id_unique');
        });
    }
};
