<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSsNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_node', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->string('name', 128)->default('')->comment('名称');
            $table->integer('group_id')->default('0')->comment('所属分组');
            $table->char('country_code', 5)->default('')->nullable()->comment('国家代码');
            $table->string('server', 128)->default('')->nullable()->comment('服务器域名地址');
            $table->string('ip', 15)->default('')->nullable()->comment('服务器IPV4地址');
            $table->string('ipv6', 128)->default('')->nullable()->comment('服务器IPV6地址');
            $table->string('desc', 255)->default('')->nullable()->comment('节点简单描述');
            $table->string('method', 32)->default('aes-192-ctr')->comment('加密方式');
            $table->string('protocol', 128)->default('auth_chain_a')->comment('协议');
            $table->string('protocol_param', 128)->default('')->nullable()->comment('协议参数');
            $table->string('obfs', 128)->default('tls1.2_ticket_auth')->comment('混淆');
            $table->string('obfs_param', 128)->default('')->nullable()->comment('混淆参数');
            $table->float('traffic_rate')->default('1.00')->comment('流量比率');
            $table->integer('bandwidth')->default('100')->comment('出口带宽，单位M');
            $table->bigInteger('traffic')->default('1000')->comment('每月可用流量，单位G');
            $table->string('monitor_url', 255)->default('')->nullable()->comment('监控地址');
            $table->tinyInteger('is_subscribe')->default('1')->nullable()->comment('是否允许用户订阅该节点：0-否、1-是');
            $table->tinyInteger('compatible')->default('0')->nullable()->comment('兼容SS');
            $table->tinyInteger('single')->default('0')->nullable()->comment('单端口多用户');
            $table->tinyInteger('single_force')->default('0')->nullable()->comment('模式：0-兼容模式、1-严格模式');
            $table->string('single_port', 50)->default('')->nullable()->comment('端口号，用,号分隔');
            $table->string('single_passwd', 50)->default('')->nullable()->comment('密码');
            $table->string('single_method', 50)->default('')->nullable()->comment('加密方式');
            $table->string('single_protocol', 50)->default('')->nullable()->comment('协议');
            $table->string('single_obfs', 50)->default('')->nullable()->comment('混淆');
            $table->integer('sort')->default('0')->comment('排序值，值越大越靠前显示');
            $table->tinyInteger('status')->default('1')->comment('状态：0-维护、1-正常');
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
        Schema::dropIfExists('ss_node');
    }
}
