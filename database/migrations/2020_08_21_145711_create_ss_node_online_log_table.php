<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsNodeOnlineLogTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('ss_node_online_log', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('node_id')->index()->comment('节点ID');
			$table->unsignedInteger('online_user')->comment('在线用户数');
			$table->unsignedInteger('log_time')->comment('记录时间');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('ss_node_online_log');
	}
}
