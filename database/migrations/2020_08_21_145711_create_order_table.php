<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_sn', 20)->comment('订单编号');
            $table->unsignedInteger('user_id')->comment('操作人');
            $table->unsignedInteger('goods_id')->nullable()->comment('商品ID');
            $table->unsignedInteger('coupon_id')->nullable()->comment('优惠券ID');
            $table->unsignedInteger('origin_amount')->default(0)->comment('订单原始总价，单位分');
            $table->unsignedInteger('amount')->default(0)->comment('订单总价，单位分');
            $table->dateTime('expired_at')->nullable()->comment('过期时间');
            $table->boolean('is_expire')->default(0)->comment('是否已过期：0-未过期、1-已过期');
            $table->boolean('pay_type')->default(0)->comment('支付渠道：0-余额、1-支付宝、2-QQ、3-微信、4-虚拟货币、5-paypal');
            $table->string('pay_way', 10)->default('balance')->comment('支付方式：balance、f2fpay、codepay、payjs、bitpayx等');
            $table->boolean('status')->default(0)->comment('订单状态：-1-已关闭、0-待支付、1-已支付待确认、2-已完成');
            $table->dateTime('created_at')->comment('创建时间');
            $table->dateTime('updated_at')->comment('最后更新时间');
            $table->index(['user_id', 'goods_id', 'is_expire', 'status'], 'idx_order_search');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order');
    }
}
