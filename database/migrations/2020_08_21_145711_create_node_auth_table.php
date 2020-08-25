<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeAuthTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('node_auth', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('node_id')->comment('授权节点ID');
			$table->char('key', 16)->comment('认证KEY');
			$table->char('secret', 8)->comment('通信密钥');
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
		Schema::dropIfExists('node_auth');
	}
}
