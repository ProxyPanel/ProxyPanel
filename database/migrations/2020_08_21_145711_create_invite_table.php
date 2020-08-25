<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInviteTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('invite', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('inviter_id')->default(0)->comment('邀请ID');
			$table->unsignedInteger('invitee_id')->nullable()->comment('受邀ID');
			$table->char('code', 12)->unique()->comment('邀请码');
			$table->boolean('status')->default(0)->comment('邀请码状态：0-未使用、1-已使用、2-已过期');
			$table->dateTime('dateline')->comment('有效期至');
			$table->dateTime('created_at')->comment('创建时间');
			$table->dateTime('updated_at')->comment('最后更新时间');
			$table->softDeletes()->comment('删除时间');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('invite');
	}
}
