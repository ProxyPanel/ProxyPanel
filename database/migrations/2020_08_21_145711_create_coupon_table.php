<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->comment('优惠券名称');
            $table->string('logo')->nullable()->comment('优惠券LOGO');
            $table->string('sn', 50)->unique()->comment('优惠券码');
            $table->boolean('type')->default(1)->comment('类型：1-抵用券、2-折扣券、3-充值券');
            $table->unsignedSmallInteger('usable_times')->nullable()->comment('可使用次数');
            $table->unsignedInteger('value')->comment('折扣金额(元)/折扣力度');
            $table->unsignedInteger('rule')->nullable()->comment('使用限制(元)');
            $table->unsignedInteger('start_time')->default(0)->comment('有效期开始');
            $table->unsignedInteger('end_time')->default(0)->comment('有效期结束');
            $table->boolean('status')->default(0)->comment('状态：0-未使用、1-已使用、2-已失效');
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
        Schema::dropIfExists('coupon');
    }
}
