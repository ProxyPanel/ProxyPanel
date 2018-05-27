<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_goods', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('oid')->default('0')->comment('订单ID');
            $table->string('order_sn', 20)->default('')->comment('订单编号');
            $table->integer('user_id')->default('0')->comment('用户ID');
            $table->integer('goods_id')->default('0')->comment('商品ID');
            $table->integer('num')->default('0')->comment('商品数量');
            $table->integer('origin_price')->default('0')->comment('商品原价，单位分');
            $table->integer('price')->default('0')->comment('商品实际价格，单位分');
            $table->tinyInteger('is_expire')->default('0')->comment('是否已过期：0-未过期、1-已过期');
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
        Schema::dropIfExists('order_goods');
    }
}
