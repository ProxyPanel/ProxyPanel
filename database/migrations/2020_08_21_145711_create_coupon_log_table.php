<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponLogTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('coupon_log', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('coupon_id')->default(0)->comment('优惠券ID');
			$table->unsignedInteger('goods_id')->default(0)->comment('商品ID');
			$table->unsignedInteger('order_id')->default(0)->comment('订单ID');
			$table->string('description', 50)->nullable()->comment('备注');
			$table->dateTime('created_at')->comment('创建时间');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('coupon_log');
	}
}
