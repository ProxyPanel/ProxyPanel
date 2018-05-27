<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferralApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_apply', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('user_id')->default('0')->comment('用户ID');
            $table->integer('before')->default('0')->comment('操作前可提现金额，单位分');
            $table->integer('after')->default('0')->comment('操作后可提现金额，单位分');
            $table->integer('amount')->default('0')->comment('本次提现金额，单位分');
            $table->string('link_logs', 255)->default('')->comment('关联返利日志ID，例如：1,3,4');
            $table->tinyInteger('status')->default('0')->comment('状态：-1-驳回、0-待审核、1-审核通过待打款、2-已打款');
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
        Schema::dropIfExists('referral_apply');
    }
}
