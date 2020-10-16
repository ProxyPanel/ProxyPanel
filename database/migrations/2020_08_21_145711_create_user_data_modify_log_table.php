<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDataModifyLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_data_modify_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID');
            $table->unsignedInteger('order_id')->default(0)->comment('发生的订单ID');
            $table->bigInteger('before')->default(0)->comment('操作前流量');
            $table->bigInteger('after')->default(0)->comment('操作后流量');
            $table->string('description')->nullable()->comment('描述');
            $table->dateTime('created_at')->comment('创建时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_data_modify_log');
    }
}
