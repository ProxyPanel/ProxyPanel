<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCreditLogTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('user_credit_log', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('user_id')->default(0)->comment('账号ID');
			$table->unsignedInteger('order_id')->default(0)->comment('订单ID');
			$table->unsignedInteger('before')->default(0)->comment('发生前余额，单位分');
			$table->unsignedInteger('after')->default(0)->comment('发生后金额，单位分');
			$table->integer('amount')->default(0)->comment('发生金额，单位分');
			$table->string('description')->nullable()->comment('操作描述');
			$table->dateTime('created_at')->comment('创建时间');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('user_credit_log');
	}
}
