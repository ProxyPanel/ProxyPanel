<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->string('sku', 15)->default('')->comment('商品服务SKU');
            $table->string('name', 100)->default('')->comment('商品名称');
            $table->string('logo', 255)->default('')->comment('商品图片地址');
            $table->bigInteger('traffic')->default('0')->comment('商品内含多少流量，单位Mib');
            $table->integer('score')->default('0')->comment('商品价值多少积分');
            $table->tinyInteger('type')->default('1')->comment('商品类型：1-流量包、2-套餐');
            $table->integer('price')->default('0')->comment('商品售价，单位分');
            $table->string('desc', 255)->default('')->nullable()->comment('商品描述');
            $table->integer('days')->default('30')->comment('有效期');
            $table->tinyInteger('is_del')->default('0')->comment('是否已删除：0-否、1-是');
            $table->tinyInteger('status')->default('1')->comment('状态：0-下架、1-上架');
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
        Schema::dropIfExists('goods');
    }
}
