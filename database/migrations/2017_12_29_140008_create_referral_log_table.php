<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('user_id')->default('0')->comment('用户ID');
            $table->integer('ref_user_id')->default('0')->comment('推广人ID');
            $table->integer('order_id')->default('0')->comment('关联订单ID');
            $table->integer('amount')->default('0')->comment('消费金额，单位分');
            $table->integer('ref_amount')->default('0')->comment('返利金额');
            $table->tinyInteger('status')->default('0')->comment('状态：0-未提现、1-审核中、2-已提现');
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
        Schema::dropIfExists('referral_log');
    }
}
