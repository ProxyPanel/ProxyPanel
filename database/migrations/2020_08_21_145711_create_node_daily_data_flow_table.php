<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeDailyDataFlowTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('node_daily_data_flow', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('node_id')->default(0)->index()->comment('节点ID');
			$table->unsignedBigInteger('u')->default(0)->comment('上传流量');
			$table->unsignedBigInteger('d')->default(0)->comment('下载流量');
			$table->unsignedBigInteger('total')->default(0)->comment('总流量');
			$table->string('traffic')->nullable()->comment('总流量（带单位）');
			$table->dateTime('created_at')->comment('创建时间');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('node_daily_data_flow');
	}
}
