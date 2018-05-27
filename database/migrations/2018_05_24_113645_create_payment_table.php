<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->char('sn', 50)->default('')->comment('订单唯一码');
            $table->integer('user_id')->default('0')->comment('用户ID');
            $table->integer('oid')->default('0')->comment('本地订单ID');
            $table->string('order_sn', 50)->default('0')->comment('本地订单长ID');
            $table->tinyInteger('pay_way')->default('1')->comment('支付方式：1-微信、2-支付宝');
            $table->integer('amount')->default('0')->comment('金额，单位分');
            $table->integer('qr_id')->default('0')->comment('有赞生成的支付单ID');
            $table->string('qr_url', 255)->default('')->comment('有赞生成的支付二维码URL');
            $table->text('qr_code')->nullable()->comment('有赞生成的支付二维码图片base64');
            $table->string('qr_local_url', 255)->nullable()->comment('支付二维码的本地存储URL');
            $table->tinyInteger('status')->default('0')->comment('状态：-1-支付失败、0-等待支付、1-支付成功');
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
        Schema::dropIfExists('payment');
    }
}
