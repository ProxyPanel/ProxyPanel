<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class CreateGoodsCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_category', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('')->comment('分类名称');
            $table->tinyInteger('status')->default('1')->comment('状态 0：隐藏 1：显示');
            $table->integer('sort')->default('0')->comment('排序');
            $table->timestamps();
        });
        Schema::table('goods', function (Blueprint $table) {
            $table->integer('category_id')->default(1)->nullable()->comment('分类ID');
        });
        \App\Models\GoodsCategory::query()->create(['name' => '黄金套餐']);
        \App\Models\GoodsCategory::query()->create(['name' => '白金套餐']);
        \App\Models\GoodsCategory::query()->create(['name' => '钻石套餐']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_category');
        Schema::table('goods', function (Blueprint $table) {
            $table->dropColumn(['category_id']);
        });
    }
}
