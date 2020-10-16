<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trade_no', 64)->comment('支付单号（本地订单号）');
            $table->unsignedInteger('user_id')->comment('用户ID');
            $table->unsignedInteger('order_id')->comment('本地订单ID');
            $table->unsignedInteger('amount')->default(0)->comment('金额，单位分');
            $table->text('qr_code')->nullable()->comment('支付二维码');
            $table->text('url')->nullable()->comment('支付链接');
            $table->boolean('status')->default(0)->comment('支付状态：-1-支付失败、0-等待支付、1-支付成功');
            $table->dateTime('created_at')->comment('创建时间');
            $table->dateTime('updated_at')->comment('最后更新时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment');
    }
}
