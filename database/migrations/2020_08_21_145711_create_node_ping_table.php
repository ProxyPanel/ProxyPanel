<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodePingTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('node_ping', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('node_id')->default(0)->index()->comment('对应节点id');
			$table->integer('ct')->default(0)->comment('电信');
			$table->integer('cu')->default(0)->comment('联通');
			$table->integer('cm')->default(0)->comment('移动');
			$table->integer('hk')->default(0)->comment('香港');
			$table->dateTime('created_at')->comment('创建时间');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('node_ping');
	}
}
