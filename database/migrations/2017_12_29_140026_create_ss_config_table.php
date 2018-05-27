<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSsConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_config', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->string('name', 50)->default('')->comment('配置名');
            $table->tinyInteger('type')->default('1')->comment('类型：1-加密方式、2-协议、3-混淆');
            $table->tinyInteger('is_default')->default('0')->comment('是否默认：0-不是、1-是');
            $table->integer('sort')->default('0')->comment('排序：值越大排越前');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_config');
    }
}
