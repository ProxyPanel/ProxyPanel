<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_log', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('type')->default(1)->comment('类型：1-邮件、2-ServerChan、3-Bark、4-Telegram');
            $table->string('address')->comment('收信地址');
            $table->string('title')->comment('标题');
            $table->text('content')->comment('内容');
            $table->boolean('status')->default(0)->comment('状态：-1发送失败、0-等待发送、1-发送成功');
            $table->text('error')->nullable()->comment('发送失败抛出的异常信息');
            $table->dateTime('created_at')->comment('创建时间');
            $table->dateTime('updated_at')->comment('最后更新时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_log');
    }
}
