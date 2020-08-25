<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsNodeInfoTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('ss_node_info', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('node_id')->default(0)->index()->comment('节点ID');
			$table->unsignedInteger('uptime')->comment('后端存活时长，单位秒');
			$table->string('load')->comment('负载');
			$table->unsignedInteger('log_time')->comment('记录时间');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('ss_node_info');
	}
}
