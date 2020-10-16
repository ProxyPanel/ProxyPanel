<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsNodeIpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_node_ip', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('node_id')->default(0)->index()->comment('节点ID');
            $table->unsignedInteger('user_id')->default(0)->index()->comment('用户ID');
            $table->unsignedSmallInteger('port')->default(0)->index()->comment('端口');
            $table->char('type', 3)->default('tcp')->comment('类型：all、tcp、udp');
            $table->text('ip')->nullable()->comment('连接IP：每个IP用,号隔开');
            $table->unsignedInteger('created_at')->default(0)->comment('上报时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_node_ip');
    }
}
