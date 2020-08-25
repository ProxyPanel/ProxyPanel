<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerifyCodeTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('verify_code', function(Blueprint $table) {
			$table->increments('id');
			$table->string('address', 128)->comment('用户邮箱');
			$table->char('code', 6)->comment('验证码');
			$table->boolean('status')->default(0)->comment('状态：0-未使用、1-已使用、2-已失效');
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
		Schema::dropIfExists('verify_code');
	}
}
