<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDailyDataFlowTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('user_daily_data_flow', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('user_id')->default(0)->comment('用户ID');
			$table->unsignedInteger('node_id')->default(0)->comment('节点ID，0表示统计全部节点');
			$table->unsignedBigInteger('u')->default(0)->comment('上传流量');
			$table->unsignedBigInteger('d')->default(0)->comment('下载流量');
			$table->unsignedBigInteger('total')->default(0)->comment('总流量');
			$table->string('traffic')->nullable()->comment('总流量（带单位）');
			$table->dateTime('created_at')->comment('创建时间');
			$table->index(['user_id', 'node_id'], 'idx_user_node');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('user_daily_data_flow');
	}
}
