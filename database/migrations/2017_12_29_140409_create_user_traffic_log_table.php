<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('user_id')->default('0')->comment('用户ID');
            $table->integer('u')->default('0')->comment('上传流量');
            $table->integer('d')->default('0')->comment('下载流量');
            $table->integer('node_id')->default('0')->comment('节点ID');
            $table->float('rate')->default('1.0')->comment('流量比例');
            $table->string('traffic', 32)->default('')->comment('产生流量');
            $table->integer('log_time')->default('0')->comment('记录时间');

            $table->index('user_id');
            $table->index('node_id');
            $table->index(['user_id', 'node_id']);
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
