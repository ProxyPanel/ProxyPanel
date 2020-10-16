<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verify', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('type')->default(1)->comment('激活类型：1-自行激活、2-管理员激活');
            $table->unsignedInteger('user_id')->comment('用户ID');
            $table->string('token', 32)->comment('校验token');
            $table->boolean('status')->default(0)->comment('状态：0-未使用、1-已使用、2-已失效');
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
        Schema::dropIfExists('verify');
    }
}
