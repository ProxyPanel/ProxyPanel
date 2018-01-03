<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('user_id')->default('0')->comment('接收者ID');
            $table->string('title', 255)->comment('邮件标题');
            $table->text('content')->comment('邮件内容')->nullable();
            $table->tinyInteger('status')->comment('状态：1-发送成功、2-发送失败');
            $table->text('error')->comment('发送失败抛出的异常信息')->nullable();
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_log');
    }
}
