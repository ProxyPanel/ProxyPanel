<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketing', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('type')->comment('类型：1-邮件群发');
            $table->text('receiver')->comment('接收者');
            $table->string('title')->comment('标题');
            $table->text('content')->comment('内容');
            $table->string('error')->nullable()->comment('错误信息');
            $table->boolean('status')->comment('状态：-1-失败、0-待发送、1-成功');
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
        Schema::dropIfExists('marketing');
    }
}
