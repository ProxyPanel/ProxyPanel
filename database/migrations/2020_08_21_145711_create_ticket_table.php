<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('ticket', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('user_id')->default(0)->comment('用户ID');
			$table->unsignedInteger('admin_id')->default(0)->comment('管理员ID');
			$table->string('title')->comment('标题');
			$table->text('content')->comment('内容');
			$table->boolean('status')->default(0)->comment('状态：0-待处理、1-已处理未关闭、2-已关闭');
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
		Schema::dropIfExists('ticket');
	}
}
