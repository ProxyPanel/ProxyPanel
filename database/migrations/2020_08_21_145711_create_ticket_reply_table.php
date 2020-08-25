<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketReplyTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('ticket_reply', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('ticket_id')->comment('工单ID');
			$table->unsignedInteger('user_id')->default(0)->comment('回复用户ID');
			$table->unsignedInteger('admin_id')->default(0)->comment('管理员ID');
			$table->text('content')->comment('回复内容');
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
		Schema::dropIfExists('ticket_reply');
	}
}
