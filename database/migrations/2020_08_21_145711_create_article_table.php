<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('article', function(Blueprint $table) {
			$table->increments('id');
			$table->boolean('type')->default(1)->comment('类型：1-文章、2-站内公告、3-站外公告');
			$table->string('title', 100)->comment('标题');
			$table->string('summary')->nullable()->comment('简介');
			$table->string('logo')->nullable()->comment('LOGO');
			$table->text('content')->nullable()->comment('内容');
			$table->unsignedTinyInteger('sort')->default(0)->comment('排序');
			$table->dateTime('created_at')->comment('创建时间');
			$table->dateTime('updated_at')->comment('最后更新时间');
			$table->softDeletes()->comment('删除时间');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('article');
	}
}
