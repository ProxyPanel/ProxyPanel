<?php

use App\Components\MigrationToolBox;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->increments('id');
            $table->unsignedInteger('user_id')->comment('用户ID');
            $table->unsignedInteger('before')->default(0)->comment('操作前可提现金额，单位分');
            $table->unsignedInteger('after')->default(0)->comment('操作后可提现金额，单位分');
            $table->unsignedInteger('amount')->default(0)->comment('本次提现金额，单位分');
            if ((new MigrationToolBox())->versionCheck()) {
                $table->json('link_logs')->comment('关联返利日志ID，例如：1,3,4');
            } else {
                $table->text('link_logs')->comment('关联返利日志ID，例如：1,3,4');
            }
            $table->boolean('status')->default(0)->comment('状态：-1-驳回、0-待审核、1-审核通过待打款、2-已打款');
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
        Schema::dropIfExists('referral_apply');
    }
}
