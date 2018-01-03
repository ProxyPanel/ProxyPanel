<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('aid')->default('0')->comment('文章ID');
            $table->string('lat', 50)->comment('纬度');
            $table->string('lng', 50)->comment('经度');
            $table->string('ip', 30)->comment('IP地址');
            $table->text('headers')->comment('浏览器头部信息')->nullable();
            $table->string('nation', 255)->comment('国家');
            $table->string('province', 255)->comment('省');
            $table->string('city', 255)->comment('市');
            $table->string('district', 255)->comment('区');
            $table->string('street', 255)->comment('街道');
            $table->string('street_number', 255)->comment('门牌');
            $table->string('address', 255)->comment('地址');
            $table->text('full')->comment('地图完整请求数据')->nullable();
            $table->tinyInteger('is_pull')->default('0')->comment('是否获取拉取地址信息');
            $table->tinyInteger('status')->default('0')->comment('状态：0-未查看、1-已查看');
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
        Schema::dropIfExists('article_log');
    }
}
