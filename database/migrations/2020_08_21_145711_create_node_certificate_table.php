<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeCertificateTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('node_certificate', function(Blueprint $table) {
			$table->increments('id');
			$table->string('domain')->comment('域名');
			$table->text('key')->nullable()->comment('域名证书KEY');
			$table->text('pem')->nullable()->comment('域名证书PEM');
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
		Schema::dropIfExists('node_certificate');
	}
}
