<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSubscribeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscribe', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('user_id')->default('0')->comment('用户ID');
            $table->char('code', 5)->default('')->nullable()->charset('utf8mb4')->collation('utf8mb4_bin')->comment('订阅地址唯一识别码');
            $table->integer('times')->default('0')->comment('地址请求次数');
            $table->tinyInteger('status')->default('1')->comment('状态：0-禁用、1-启用');
            $table->integer('ban_time')->default('0')->comment('封禁时间');
            $table->string('ban_desc', 50)->default('')->nullable()->comment('封禁理由');
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
        Schema::dropIfExists('user_subscribe');
    }
}
