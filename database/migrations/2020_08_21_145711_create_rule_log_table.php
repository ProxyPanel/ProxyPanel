<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuleLogTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('rule_log', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('user_id')->default(0)->comment('用户ID');
			$table->unsignedInteger('node_id')->default(0)->comment('节点ID');
			$table->unsignedInteger('rule_id')->default(0)->comment('规则ID，0表示白名单模式下访问访问了非规则允许的网址');
			$table->string('reason')->nullable()->comment('触发原因');
			$table->dateTime('created_at')->comment('创建时间');
			$table->index(['user_id', 'node_id', 'rule_id'], 'idx');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('rule_log');
	}
}
