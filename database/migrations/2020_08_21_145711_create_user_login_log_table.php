<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLoginLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_login_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID');
            $table->ipAddress('ip')->comment('IP地址');
            $table->string('country', 128)->comment('国家');
            $table->string('province', 128)->comment('省份');
            $table->string('city', 128)->comment('城市');
            $table->string('county', 128)->comment('郡县');
            $table->string('isp', 128)->comment('运营商');
            $table->string('area')->comment('地区');
            $table->dateTime('created_at')->comment('创建时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_login_log');
    }
}
