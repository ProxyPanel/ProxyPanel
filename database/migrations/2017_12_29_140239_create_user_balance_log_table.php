<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBalanceLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_balance_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('user_id')->default('0')->comment('用户ID');
            $table->integer('order_id')->default('0')->comment('订单ID');
            $table->integer('before')->default('0')->comment('发生前余额，单位分');
            $table->integer('after')->default('0')->comment('发生后金额，单位分');
            $table->integer('amount')->default('0')->comment('发生金额，单位分');
            $table->string('desc', 255)->default('')->nullable()->comment('操作描述');
            $table->dateTime('created_at')->nullable()->comment('创建时间');

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_balance_log');
    }
}
