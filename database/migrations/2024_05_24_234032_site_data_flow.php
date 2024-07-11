<?php

use App\Models\NodeDailyDataFlow;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('node_daily_data_flow', static function (Blueprint $table) {
            $table->unsignedInteger('node_id')->nullable()->change();
        });

        // 使用查询构建器对数据进行分组并计算合计值
        $dailyTotals = NodeDailyDataFlow::whereNotNull('node_id')->oldest()->selectRaw('DATE(created_at) as date, SUM(u) as total_u, SUM(d) as total_d')
            ->groupBy('date')
            ->get();

        // 遍历查询结果，创建新的合计列
        foreach ($dailyTotals as $dailyTotal) {
            // 创建新记录，同时设置合计列的初始值
            NodeDailyDataFlow::create([
                'u' => $dailyTotal->total_u,
                'd' => $dailyTotal->total_d,
                'created_at' => Carbon::parse($dailyTotal->date)->endOfDay(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NodeDailyDataFlow::whereNull('node_id')->delete();
        Schema::table('node_daily_data_flow', static function (Blueprint $table) {
            $table->unsignedInteger('node_id')->nullable(false)->change();
        });
    }
};
