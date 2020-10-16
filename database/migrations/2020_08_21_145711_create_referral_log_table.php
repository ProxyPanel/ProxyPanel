<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invitee_id')->comment('用户ID');
            $table->unsignedInteger('inviter_id')->comment('推广人ID');
            $table->unsignedInteger('order_id')->comment('关联订单ID');
            $table->unsignedInteger('amount')->comment('消费金额，单位分');
            $table->unsignedInteger('commission')->comment('返利金额');
            $table->boolean('status')->default(0)->comment('状态：0-未提现、1-审核中、2-已提现');
            $table->dateTime('created_at')->comment('创建时间');
            $table->dateTime('updated_at')->comment('最后更新时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_log');
    }
}
