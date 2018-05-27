<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserScoreLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_score_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('user_id')->default('0')->comment('用户ID');
            $table->integer('before')->default('0')->comment('发生前的积分值');
            $table->integer('after')->default('0')->comment('发生后的积分值');
            $table->integer('score')->default('0')->comment('发生值');
            $table->string('desc', 50)->default('')->nullable()->comment('描述');
            $table->dateTime('created_at');

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_score_log');
    }
}
