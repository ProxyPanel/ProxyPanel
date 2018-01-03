<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('title', 100)->comment('文章标题');
            $table->string('author', 50)->comment('作者');
            $table->text('content')->comment('文章内容')->nullable();
            $table->tinyInteger('is_del')->default('0')->comment('是否删除');
            $table->tinyInteger('type')->comment('类型：1-文章、2-公告');
            $table->integer('sort')->default('0')->comment('排序');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article');
    }
}
