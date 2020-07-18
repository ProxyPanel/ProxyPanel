<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('node', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedTinyInteger('type')->default(1)->comment('服务类型：1-Shadowsocks(R)、2-V2ray、3-Trojan、4-VNet');
			$table->string('name', 128)->comment('名称');
			$table->char('country_code', 5)->default('un')->comment('国家代码');
			$table->string('server')->nullable()->comment('服务器域名地址');
			$table->char('ip', 15)->nullable()->comment('服务器IPV4地址');
			$table->ipAddress('ipv6')->nullable()->comment('服务器IPV6地址');
			$table->string('relay_server')->nullable()->comment('中转地址');
			$table->unsignedSmallInteger('relay_port')->nullable()->default(0)->comment('中转端口');
			$table->unsignedTinyInteger('level')->default(0)->comment('等级：0-无等级，全部可见');
			$table->unsignedBigInteger('speed_limit')->default(0)->comment('节点限速，为0表示不限速，单位Byte');
			$table->unsignedSmallInteger('client_limit')->default(0)->comment('设备数限制');
			$table->string('description')->nullable()->comment('节点简单描述');
			$table->string('method', 32)->default('aes-256-cfb')->comment('加密方式');
			$table->string('protocol', 64)->default('origin')->comment('协议');
			$table->string('protocol_param', 128)->nullable()->comment('协议参数');
			$table->string('obfs', 64)->default('plain')->comment('混淆');
			$table->string('obfs_param')->nullable()->comment('混淆参数');
			$table->unsignedDecimal('traffic_rate', 6)->default(1.00)->comment('流量比率');
			$table->boolean('is_subscribe')->default(1)->comment('是否允许用户订阅该节点：0-否、1-是');
			$table->boolean('is_ddns')->default(0)->comment('是否使用DDNS：0-否、1-是');
			$table->boolean('is_relay')->default(0)->comment('是否中转节点：0-否、1-是');
			$table->boolean('is_udp')->default(1)->comment('是否启用UDP：0-不启用、1-启用');
			$table->unsignedSmallInteger('push_port')->default(0)->comment('消息推送端口');
			$table->boolean('detection_type')->default(1)->comment('节点检测: 0-关闭、1-只检测TCP、2-只检测ICMP、3-检测全部');
			$table->boolean('compatible')->default(0)->comment('兼容SS');
			$table->boolean('single')->default(0)->comment('启用单端口功能：0-否、1-是');
			$table->unsignedSmallInteger('port')->nullable()->comment('单端口的端口号或连接端口号');
			$table->string('passwd')->nullable()->comment('单端口的连接密码');
			$table->unsignedTinyInteger('sort')->default(0)->comment('排序值，值越大越靠前显示');
			$table->boolean('status')->default(1)->comment('状态：0-维护、1-正常');
			$table->unsignedSmallInteger('v2_alter_id')->default(16)->comment('V2Ray额外ID');
			$table->unsignedSmallInteger('v2_port')->default(0)->comment('V2Ray服务端口');
			$table->string('v2_method', 32)->default('aes-128-gcm')->comment('V2Ray加密方式');
			$table->string('v2_net', 16)->default('tcp')->comment('V2Ray传输协议');
			$table->string('v2_type', 32)->default('none')->comment('V2Ray伪装类型');
			$table->string('v2_host')->comment('V2Ray伪装的域名');
			$table->string('v2_path')->comment('V2Ray的WS/H2路径');
			$table->boolean('v2_tls')->default(0)->comment('V2Ray后端TLS：0-未开启、1-开启');
			$table->text('tls_provider')->nullable()->comment('V2Ray节点的TLS提供商授权信息');
			$table->timestamps();
			$table->index('is_subscribe', 'idx_sub');
			$table->engine = 'InnoDB';
			$table->charset = 'utf8mb4';
			$table->collation = 'utf8mb4_unicode_ci';
		});

		DB::statement("ALTER TABLE `node` comment '节点信息表'");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('node');
	}
}
