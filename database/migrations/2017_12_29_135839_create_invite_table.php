<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInviteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('uid')->default('0')->comment('邀请人ID');
            $table->integer('fuid')->default('0')->comment('受邀人ID');
            $table->char('code', 32)->default('')->comment('邀请码');
            $table->tinyInteger('status')->default('0')->comment('邀请码状态：0-未使用、1-已使用、2-已过期');
            $table->dateTime('dateline')->nullable()->comment('有效期至');
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
        Schema::dropIfExists('invite');
    }
}
