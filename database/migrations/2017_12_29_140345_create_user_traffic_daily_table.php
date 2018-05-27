<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTrafficDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_traffic_daily', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('user_id')->default('0')->comment('用户ID');
            $table->integer('node_id')->default('0')->comment('节点ID，0表示统计全部节点');
            $table->bigInteger('u')->default('0')->comment('上传流量');
            $table->bigInteger('d')->default('0')->comment('下载流量');
            $table->bigInteger('total')->default('0')->comment('总流量');
            $table->string('traffic', 255)->default('')->comment('总流量（带单位）');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

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
        Schema::dropIfExists('user_traffic_daily');
    }
}
