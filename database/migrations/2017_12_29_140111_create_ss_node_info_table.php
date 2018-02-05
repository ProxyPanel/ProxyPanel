<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSsNodeInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_node_info', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('node_id')->default('0')->comment('节点ID');
            $table->float('uptime')->comment('更新时间');
            $table->string('load', 32)->default('')->nullable()->comment('负载');
            $table->integer('log_time')->default('0')->comment('记录时间');

            $table->index('node_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_node_info');
    }
}
