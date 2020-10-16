<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 64)->comment('昵称');
            $table->string('email', 128)->unique()->comment('邮箱');
            $table->string('password', 64)->comment('密码');
            $table->unsignedSmallInteger('port')->default(0)->comment('代理端口');
            $table->string('passwd', 16)->comment('代理密码');
            $table->uuid('vmess_id');
            $table->unsignedBigInteger('transfer_enable')->default(1099511627776)->comment('可用流量，单位字节，默认1TiB');
            $table->unsignedBigInteger('u')->default(0)->comment('已上传流量，单位字节');
            $table->unsignedBigInteger('d')->default(0)->comment('已下载流量，单位字节');
            $table->unsignedInteger('t')->nullable()->comment('最后使用时间');
            $table->ipAddress('ip')->nullable()->comment('最后连接IP');
            $table->boolean('enable')->default(1)->comment('代理状态');
            $table->string('method', 30)->default('aes-256-cfb')->comment('加密方式');
            $table->string('protocol', 30)->default('origin')->comment('协议');
            $table->string('protocol_param')->nullable()->comment('协议参数');
            $table->string('obfs', 30)->default('plain')->comment('混淆');
            $table->unsignedBigInteger('speed_limit')->default(0)->comment('用户限速，为0表示不限速，单位Byte');
            $table->string('wechat', 30)->nullable()->comment('微信');
            $table->string('qq', 20)->nullable()->comment('QQ');
            $table->unsignedInteger('credit')->default(0)->comment('余额，单位分');
            $table->date('expired_at')->default('2099-01-01')->comment('过期时间');
            $table->unsignedInteger('ban_time')->nullable()->comment('封禁到期时间');
            $table->text('remark')->nullable()->comment('备注');
            $table->unsignedTinyInteger('level')->default(0)->comment('等级，默认0级');
            $table->unsignedInteger('group_id')->default(0)->comment('所属分组');
            $table->boolean('is_admin')->default(0)->comment('是否管理员：0-否、1-是');
            $table->ipAddress('reg_ip')->default('127.0.0.1')->comment('注册IP');
            $table->unsignedInteger('last_login')->default(0)->comment('最后登录时间');
            $table->unsignedInteger('inviter_id')->nullable()->comment('邀请人');
            $table->date('reset_time')->nullable()->comment('流量重置日期');
            $table->unsignedInteger('invite_num')->default(0)->comment('可生成邀请码数');
            $table->boolean('status')->default(0)->comment('状态：-1-禁用、0-未激活、1-正常');
            $table->string('remember_token')->nullable();
            $table->dateTime('created_at')->comment('创建时间');
            $table->dateTime('updated_at')->comment('最后更新时间');
            $table->index(['enable', 'status', 'port'], 'idx_search');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
