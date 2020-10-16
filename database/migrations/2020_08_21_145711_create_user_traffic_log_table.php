<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTrafficLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_traffic_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID');
            $table->unsignedInteger('node_id')->default(0)->comment('节点ID');
            $table->unsignedInteger('u')->default(0)->comment('上传流量');
            $table->unsignedInteger('d')->default(0)->comment('下载流量');
            $table->float('rate', 6)->unsigned()->comment('倍率');
            $table->string('traffic', 32)->comment('产生流量');
            $table->unsignedInteger('log_time')->comment('记录时间');
            $table->index(['user_id', 'node_id', 'log_time'], 'idx_user_node_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_traffic_log');
    }
}
