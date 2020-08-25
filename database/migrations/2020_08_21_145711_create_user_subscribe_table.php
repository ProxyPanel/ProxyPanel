<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscribeTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('user_subscribe', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('user_id')->default(0)->comment('用户ID');
			$table->char('code', 8)->index()->comment('订阅地址唯一识别码');
			$table->unsignedInteger('times')->default(0)->comment('地址请求次数');
			$table->boolean('status')->default(1)->comment('状态：0-禁用、1-启用');
			$table->unsignedInteger('ban_time')->nullable()->comment('封禁时间');
			$table->string('ban_desc', 50)->nullable()->comment('封禁理由');
			$table->dateTime('created_at')->comment('创建时间');
			$table->dateTime('updated_at')->comment('最后更新时间');
			$table->index(['user_id', 'status'], 'user_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('user_subscribe');
	}
}
