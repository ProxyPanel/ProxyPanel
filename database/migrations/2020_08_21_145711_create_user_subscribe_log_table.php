<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscribeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscribe_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_subscribe_id')->index()->comment('对应user_subscribe的id');
            $table->ipAddress('request_ip')->nullable()->comment('请求IP');
            $table->dateTime('request_time')->comment('请求时间');
            $table->text('request_header')->nullable()->comment('请求头部信息');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_subscribe_log');
    }
}
