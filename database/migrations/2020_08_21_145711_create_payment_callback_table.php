<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCallbackTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('payment_callback', function(Blueprint $table) {
			$table->increments('id');
			$table->string('trade_no', 64)->comment('本地订单号');
			$table->string('out_trade_no', 64)->comment('外部订单号（支付平台）');
			$table->unsignedInteger('amount')->comment('交易金额，单位分');
			$table->boolean('status')->comment('交易状态：0-失败、1-成功');
			$table->dateTime('created_at')->comment('创建时间');
			$table->dateTime('updated_at')->comment('最后更新时间');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('payment_callback');
	}
}
