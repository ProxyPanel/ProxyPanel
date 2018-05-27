<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('oid');
            $table->string('order_sn', 20)->default('')->comment('订单编号');
            $table->integer('user_id')->default('0')->comment('操作人');
            $table->integer('goods_id')->default('0')->comment('商品ID');
            $table->integer('coupon_id')->default('0')->comment('优惠券ID');
            $table->integer('origin_amount')->default('0')->comment('订单原始总价，单位分');
            $table->integer('amount')->default('0')->comment('订单总价，单位分');
            $table->dateTime('expire_at')->nullable()->comment('过期时间');
            $table->tinyInteger('is_expire')->default('0')->comment('是否已过期：0-未过期、1-已过期');
            $table->tinyInteger('pay_way')->default('1')->comment('支付方式：1-余额支付、2-有赞云支付');
            $table->tinyInteger('status')->default('0')->comment('订单状态：-1-已关闭、0-待支付、1-已支付待确认、2-已完成');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
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
