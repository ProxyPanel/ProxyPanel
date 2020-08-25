<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailFilterTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('email_filter', function(Blueprint $table) {
			$table->increments('id');
			$table->boolean('type')->default(1)->comment('类型：1-黑名单、2-白名单');
			$table->string('words', 50)->comment('敏感词');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('email_filter');
	}
}
