<?php

namespace App\Components;

use App\Models\Config;
use App\Models\CouponLog;
use App\Models\NotificationLog;
use App\Models\SsConfig;
use App\Models\User;
use App\Models\UserCreditLog;
use App\Models\UserDataModifyLog;
use App\Models\UserSubscribe;
use DateTime;
use Str;

class Helpers {
	// 不生成的端口
	private static $denyPorts = [
		1068,
		1109,
		1434,
		3127,
		3128,
		3129,
		3130,
		3332,
		4444,
		5554,
		6669,
		8080,
		8081,
		8082,
		8181,
		8282,
		9996,
		17185,
		24554,
		35601,
		60177,
		60179
	];

	// 加密方式
	public static function methodList() {
		return SsConfig::type(1)->get();
	}

	// 协议
	public static function protocolList() {
		return SsConfig::type(2)->get();
	}

	// 混淆
	public static function obfsList() {
		return SsConfig::type(3)->get();
	}

	// 生成用户的订阅码
	public static function makeSubscribeCode(): string {
		$code = Str::random();
		if(UserSubscribe::whereCode($code)->exists()){
			$code = self::makeSubscribeCode();
		}

		return $code;
	}

	/**
	 * 添加用户
	 *
	 * @param  string    $email            用户邮箱
	 * @param  string    $password         用户密码
	 * @param  string    $transfer_enable  可用流量
	 * @param  int       $data             可使用天数
	 * @param  int|null  $inviter_id       邀请人
	 *
	 * @return int
	 */
	public static function addUser($email, $password, $transfer_enable, $data, $inviter_id = null): int {
		$user = new User();
		$user->username = $email;
		$user->email = $email;
		$user->password = $password;
		// 生成一个可用端口
		$user->port = self::sysConfig()['is_rand_port']? self::getRandPort() : self::getOnlyPort();
		$user->passwd = Str::random();
		$user->vmess_id = Str::uuid();
		$user->enable = 1;
		$user->method = self::getDefaultMethod();
		$user->protocol = self::getDefaultProtocol();
		$user->obfs = self::getDefaultObfs();
		$user->transfer_enable = $transfer_enable;
		$user->expired_at = date('Y-m-d', strtotime("+".$data." days"));
		$user->reg_ip = getClientIp();
		$user->inviter_id = $inviter_id;
		$user->reset_time = null;
		$user->status = 0;
		$user->save();

		return $user->id;
	}

	// 获取系统配置
	public static function sysConfig(): array {
		$data = Config::all()->pluck('value', 'name')->toArray();
		$data['is_onlinePay'] = ($data['is_AliPay'] || $data['is_QQPay'] || $data['is_WeChatPay'] || $data['is_otherPay'])?: 0;

		return $data;
	}

	// 获取一个随机端口
	public static function getRandPort(): int {
		$port = random_int(self::sysConfig()['min_port'], self::sysConfig()['max_port']);

		$exists_port = User::pluck('port')->toArray();
		if(in_array($port, $exists_port, true) || in_array($port, self::$denyPorts, true)){
			$port = self::getRandPort();
		}

		return $port;
	}

	// 获取一个随机端口
	public static function getOnlyPort(): int {
		$port = (int) self::sysConfig()['min_port'];

		$exists_port = User::where('port', '>=', $port)->pluck('port')->toArray();
		while(in_array($port, $exists_port, true) || in_array($port, self::$denyPorts, true)){
			++$port;
		}

		return $port;
	}

	// 获取默认加密方式
	public static function getDefaultMethod(): string {
		$config = SsConfig::default()->type(1)->first();

		return $config? $config->name : 'aes-256-cfb';
	}

	// 获取默认协议
	public static function getDefaultProtocol(): string {
		$config = SsConfig::default()->type(2)->first();

		return $config? $config->name : 'origin';
	}

	// 获取默认混淆
	public static function getDefaultObfs(): string {
		$config = SsConfig::default()->type(3)->first();

		return $config? $config->name : 'plain';
	}

	public static function daysToNow($date): int {
		return (new DateTime())->diff(new DateTime($date))->days;
	}

	/**
	 * 添加通知推送日志
	 *
	 * @param  string  $title    标题
	 * @param  string  $content  内容
	 * @param  int     $type     发送类型
	 * @param  string  $address  收信方
	 * @param  int     $status   投递状态
	 * @param  string  $error    投递失败时记录的异常信息
	 *
	 * @return int
	 */
	public static function addNotificationLog($title, $content, $type, $address = 'admin', $status = 1, $error = ''
	): int {
		$log = new NotificationLog();
		$log->type = $type;
		$log->address = $address;
		$log->title = $title;
		$log->content = $content;
		$log->status = $status;
		$log->error = $error;
		$log->save();

		return $log->id;
	}

	/**
	 * 添加优惠券操作日志
	 *
	 * @param  string  $description  备注
	 * @param  int     $couponId     优惠券ID
	 * @param  int     $goodsId      商品ID
	 * @param  int     $orderId      订单ID
	 *
	 * @return boolean
	 */
	public static function addCouponLog($description, $couponId, $goodsId = 0, $orderId = 0): bool {
		$log = new CouponLog();
		$log->coupon_id = $couponId;
		$log->goods_id = $goodsId;
		$log->order_id = $orderId;
		$log->description = $description;

		return $log->save();
	}

	/**
	 * 记录余额操作日志
	 *
	 * @param  int     $userId       用户ID
	 * @param  int     $orderId      订单ID
	 * @param  int     $before       记录前余额
	 * @param  int     $after        记录后余额
	 * @param  int     $amount       发生金额
	 * @param  string  $description  描述
	 *
	 * @return boolean
	 */
	public static function addUserCreditLog($userId, $orderId, $before, $after, $amount, $description = ''): bool {
		$log = new UserCreditLog();
		$log->user_id = $userId;
		$log->order_id = $orderId;
		$log->before = $before;
		$log->after = $after;
		$log->amount = $amount;
		$log->description = $description;
		$log->created_at = date('Y-m-d H:i:s');

		return $log->save();
	}

	/**
	 * 记录流量变动日志
	 *
	 * @param  int     $userId       用户ID
	 * @param  int     $orderId      订单ID
	 * @param  int     $before       记录前的值
	 * @param  int     $after        记录后的值
	 * @param  string  $description  描述
	 *
	 * @return bool
	 */
	public static function addUserTrafficModifyLog($userId, $orderId, $before, $after, $description = ''): bool {
		$log = new UserDataModifyLog();
		$log->user_id = $userId;
		$log->order_id = $orderId;
		$log->before = $before;
		$log->after = $after;
		$log->description = $description;

		return $log->save();
	}
}
