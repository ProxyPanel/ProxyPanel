<?php

use App\Models\NodeDailyDataFlow;
use App\Models\NodeHourlyDataFlow;
use App\Models\UserDailyDataFlow;
use App\Models\UserHourlyDataFlow;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('country')->where('code', '=', 'uk')->update(['code' => 'gb']);

        Schema::table('node_daily_data_flow', function (Blueprint $table) {
            $table->dropColumn(['total', 'traffic']);
        });
        Schema::table('node_hourly_data_flow', function (Blueprint $table) {
            $table->dropColumn(['total', 'traffic']);
        });
        Schema::table('user_daily_data_flow', function (Blueprint $table) {
            $table->dropColumn(['total', 'traffic']);
        });
        Schema::table('user_hourly_data_flow', function (Blueprint $table) {
            $table->dropColumn(['total', 'traffic']);
        });
    }

    public function down(): void
    {
        Schema::table('node_daily_data_flow', static function (Blueprint $table) {
            $table->unsignedBigInteger('total')->default(0)->comment('总流量')->after('d');
            $table->string('traffic')->nullable()->comment('总流量（带单位）')->after('total');
        });
        foreach (NodeDailyDataFlow::cursor() as $log) {
            $log->total = $log->u + $log->d;
            $log->traffic = formatBytes($log->total);
            $log->save();
        }

        Schema::table('node_hourly_data_flow', static function (Blueprint $table) {
            $table->unsignedBigInteger('total')->default(0)->comment('总流量')->after('d');
            $table->string('traffic')->nullable()->comment('总流量（带单位）')->after('total');
        });
        foreach (NodeHourlyDataFlow::cursor() as $log) {
            $log->total = $log->u + $log->d;
            $log->traffic = formatBytes($log->total);
            $log->save();
        }

        Schema::table('user_daily_data_flow', static function (Blueprint $table) {
            $table->unsignedBigInteger('total')->default(0)->comment('总流量')->after('d');
            $table->string('traffic')->nullable()->comment('总流量（带单位）')->after('total');
        });
        foreach (UserDailyDataFlow::cursor() as $log) {
            $log->total = $log->u + $log->d;
            $log->traffic = formatBytes($log->total);
            $log->save();
        }

        Schema::table('user_hourly_data_flow', static function (Blueprint $table) {
            $table->unsignedBigInteger('total')->default(0)->comment('总流量')->after('d');
            $table->string('traffic')->nullable()->comment('总流量（带单位）')->after('total');
        });

        foreach (UserHourlyDataFlow::cursor() as $log) {
            $log->total = $log->u + $log->d;
            $log->traffic = formatBytes($log->total);
            $log->save();
        }
    }
};
