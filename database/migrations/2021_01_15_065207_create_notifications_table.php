<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    private $configs = [
        'account_expire_notification',
        'data_anomaly_notification',
        'data_exhaust_notification',
        'node_blocked_notification',
        'node_daily_notification',
        'node_offline_notification',
        'password_reset_notification',
        'payment_received_notification',
        'ticket_closed_notification',
        'ticket_created_notification',
        'ticket_replied_notification',
    ];

    private $dropConfigs = [
        'is_reset_password',
        'expire_warning',
        'traffic_warning',
        'is_node_offline',
        'node_daily_report',
        'nodes_detection',
        'is_notification',
    ];

    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        Schema::table('order', function (Blueprint $table) {
            $table->renameColumn('order_sn', 'sn');
        });

        foreach ($this->configs as $config) {
            \App\Models\Config::insert(['name' => $config]);
        }
        \App\Models\Config::whereIn('name', $this->dropConfigs)->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
        Schema::table('order', function (Blueprint $table) {
            $table->renameColumn('sn', 'order_sn');
        });

        foreach ($this->dropConfigs as $config) {
            \App\Models\Config::insert(['name' => $config]);
        }
        \App\Models\Config::whereIn('name', $this->configs)->delete();
    }
}
