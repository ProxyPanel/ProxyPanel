<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeLabelTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('node_label', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('node_id')->default(0)->comment('节点ID');
			$table->unsignedInteger('label_id')->default(0)->comment('标签ID');
			$table->index(['node_id', 'label_id'], 'idx_node_label');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('node_label');
	}
}
