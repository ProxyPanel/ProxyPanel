<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->increments('id');
            $table->string('name', 100)->comment('商品名称');
            $table->string('logo')->nullable()->comment('商品图片地址');
            $table->unsignedBigInteger('traffic')->default(0)->comment('商品内含多少流量，单位MiB');
            $table->boolean('type')->default(1)->comment('商品类型：1-流量包、2-套餐');
            $table->unsignedInteger('price')->default(0)->comment('售价，单位分');
            $table->unsignedTinyInteger('level')->default(0)->comment('购买后给用户授权的等级');
            $table->unsignedInteger('renew')->nullable()->comment('流量重置价格，单位分');
            $table->unsignedInteger('period')->nullable()->comment('流量自动重置周期');
            $table->string('info')->nullable()->comment('商品信息');
            $table->string('description')->nullable()->comment('商品描述');
            $table->unsignedInteger('days')->default(30)->comment('有效期');
            $table->unsignedInteger('invite_num')->nullable()->comment('赠送邀请码数');
            $table->unsignedInteger('limit_num')->nullable()->comment('限购数量，默认为null不限购');
            $table->string('color', 50)->default('green')->comment('商品颜色');
            $table->unsignedTinyInteger('sort')->default(0)->comment('排序');
            $table->boolean('is_hot')->default(0)->comment('是否热销：0-否、1-是');
            $table->boolean('status')->default(0)->comment('状态：0-下架、1-上架');
            $table->dateTime('created_at')->comment('创建时间');
            $table->dateTime('updated_at')->comment('最后更新时间');
            $table->softDeletes()->comment('删除时间');
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
