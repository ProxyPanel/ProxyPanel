<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\PushNotification;
use App\Models\Config;
use App\Models\Coupon;
use App\Models\Invite;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SsNode;
use App\Models\SsNodeInfo;
use App\Models\User;
use App\Models\UserBanLog;
use App\Models\UserSubscribe;
use App\Models\UserSubscribeLog;
use App\Models\UserTrafficHourly;
use App\Models\VerifyCode;
use Cache;
use DB;
use Exception;
use Illuminate\Console\Command;
use Log;

class AutoJob extends Command {
	protected static $systemConfig;
	protected $signature = 'autoJob';
	protected $description = '自动化任务';

	public function __construct() {
		parent::__construct();
		self::$systemConfig = Helpers::systemConfig();
	}

	/*
	 * 警告：除非熟悉业务流程，否则不推荐更改以下执行顺序，随意变更以下顺序可能导致系统异常
	 */
	public function handle(): void {
		$jobStartTime = microtime(true);

		// 关闭超时未支付在线订单
		$this->closePayments();

		// 关闭超时未支付订单
		$this->closeOrders();

		//过期验证码、优惠券、邀请码无效化
		$this->expireCode();

		// 封禁访问异常的订阅链接
		$this->blockSubscribe();

		// 封禁账号
		$this->blockUsers();

		// 解封被封禁的账号
		$this->unblockUsers();

		// 端口回收与分配
		$this->dispatchPort();

		// 检测节点是否离线
		$this->checkNodeStatus();

		// 检查 维护模式
		if(self::$systemConfig['maintenance_mode'] && strtotime(self::$systemConfig['maintenance_time']) < time()){
			Config::query()->whereName('maintenance_mode')->update(['value' => 0]);
			Config::query()->whereName('maintenance_time')->update(['value' => '']);
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	// 关闭超时未在线支付订单
	private function closePayments(): void {
		// 关闭超时未支付的在线订单（15分钟关闭订单）
		$paymentList = Payment::query()
		                      ->whereStatus(0)
		                      ->where('created_at', '<=', date("Y-m-d H:i:s", strtotime("-15 minutes")))
		                      ->get();
		if($paymentList->isNotEmpty()){
			try{
				DB::beginTransaction();

				foreach($paymentList as $payment){
					// 关闭支付单
					Payment::query()->whereId($payment->id)->update(['status' => -1]);
					// 关闭回调PaymentCallback::query()->whereTradeNo($payment->trade_no)->update(['status' => 0]);

					// 关闭订单
					Order::query()->whereOid($payment->oid)->update(['status' => -1]);

					// 退回优惠券
					if($payment->order->coupon_id){
						$result = $this->returnCoupon($payment->order->coupon_id);
						if($result){
							Helpers::addCouponLog($payment->order->coupon_id, $payment->order->goods_id, $payment->oid,
								'在线订单超时未支付，自动退回');
						}
					}
				}

				DB::commit();
			}catch(Exception $e){
				Log::info('【异常】自动关闭超时未支付在线订单：'.$e);

				DB::rollBack();
			}
		}
	}

	//返回优惠券
	private function returnCoupon($coupon_id): bool {
		$coupon = Coupon::find($coupon_id);
		if($coupon && $coupon->type !== 3){
			Coupon::query()->whereId($coupon_id)->increment('usage_count', 1, ['status' => 0]);
			return true;
		}
		return false;
	}

	// 关闭超时未支付订单
	private function closeOrders(): void {
		// 关闭超时未支付的支付订单（15分钟关闭订单）
		$orderList = Order::query()
		                  ->whereStatus(0)
		                  ->where('created_at', '<=', date("Y-m-d H:i:s", strtotime("-15 minutes")))
		                  ->get();
		if($orderList->isNotEmpty()){
			try{
				DB::beginTransaction();

				foreach($orderList as $order){
					// 关闭订单
					Order::query()->whereOid($order->oid)->update(['status' => -1]);

					// 退回优惠券
					if($order->coupon_id){
						$result = $this->returnCoupon($order->coupon_id);
						if($result){
							Helpers::addCouponLog($order->coupon_id, $order->goods_id, $order->oid, '订单超时未支付，自动退回');
						}
					}
				}

				DB::commit();
			}catch(Exception $e){
				Log::info('【异常】自动关闭超时未支付订单：'.$e);

				DB::rollBack();
			}
		}
	}

	// 注册验证码自动置无效 & 优惠券无效化
	private function expireCode(): void {
		// 注册验证码自动置无效
		VerifyCode::query()
		          ->whereStatus(0)
		          ->where('created_at', '<=', date('Y-m-d H:i:s', strtotime("-10 minutes")))
		          ->update(['status' => 2]);

		// 优惠券到期自动置无效
		Coupon::query()->whereStatus(0)->where('available_end', '<=', time())->update(['status' => 2]);

		// 用尽的优惠劵
		Coupon::query()->whereStatus(0)->whereIn('type', [1, 2])->where('usage_count', '=', 0)->update(['status' => 2]);

		// 邀请码到期自动置无效
		Invite::query()->whereStatus(0)->where('dateline', '<=', date('Y-m-d H:i:s'))->update(['status' => 2]);
	}

	// 封禁访问异常的订阅链接
	private function blockSubscribe(): void {
		if(self::$systemConfig['is_subscribe_ban']){
			$userList = User::query()->where('status', '>=', 0)->whereEnable(1)->get();
			foreach($userList as $user){
				$subscribe = UserSubscribe::query()->whereUserId($user->id)->first();
				if($subscribe){
					// 24小时内不同IP的请求次数
					$request_times = UserSubscribeLog::query()
					                                 ->whereSid($subscribe->id)
					                                 ->where('request_time', '>=',
						                                 date("Y-m-d H:i:s", strtotime("-24 hours")))
					                                 ->distinct('request_ip')
					                                 ->count('request_ip');
					if($request_times >= self::$systemConfig['subscribe_ban_times']){
						UserSubscribe::query()->whereId($subscribe->id)->update([
							'status'   => 0,
							'ban_time' => time(),
							'ban_desc' => '存在异常，自动封禁'
						]);

						// 记录封禁日志
						$this->addUserBanLog($subscribe->user_id, 0, '【完全封禁订阅】-订阅24小时内请求异常');
					}
				}
			}
		}
	}

	/**
	 * 添加用户封禁日志
	 *
	 * @param  int     $userId       用户ID
	 * @param  int     $minutes      封禁时长，单位分钟
	 * @param  string  $description  封禁理由
	 */
	private function addUserBanLog($userId, $minutes, $description): void {
		$log = new UserBanLog();
		$log->user_id = $userId;
		$log->minutes = $minutes;
		$log->description = $description;
		$log->save();
	}

	// 封禁账号
	private function blockUsers(): void {
		// 封禁1小时内流量异常账号
		if(self::$systemConfig['is_traffic_ban']){
			$userList = User::query()->whereEnable(1)->where('status', '>=', 0)->whereBanTime(0)->get();
			foreach($userList as $user){
				// 对管理员豁免
				if($user->is_admin){
					continue;
				}

				// 多往前取5分钟，防止数据统计任务执行时间过长导致没有数据
				$totalTraffic = UserTrafficHourly::query()
				                                 ->whereUserId($user->id)
				                                 ->whereNodeId(0)
				                                 ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))
				                                 ->sum('total');
				if($totalTraffic >= (self::$systemConfig['traffic_ban_value'] * GB)){
					User::query()->whereId($user->id)->update([
						'enable'   => 0,
						'ban_time' => strtotime(date('Y-m-d H:i:s',
							strtotime("+".self::$systemConfig['traffic_ban_time']." minutes")))
					]);

					// 写入日志
					$this->addUserBanLog($user->id, self::$systemConfig['traffic_ban_time'], '【临时封禁代理】-1小时内流量异常');
				}
			}
		}

		// 禁用流量超限用户
		$userList = User::query()
		                ->whereEnable(1)
		                ->where('status', '>=', 0)
		                ->whereBanTime(0)
		                ->whereRaw("u + d >= transfer_enable")
		                ->get();
		foreach($userList as $user){
			User::query()->whereId($user->id)->update(['enable' => 0]);

			// 写入日志
			$this->addUserBanLog($user->id, 0, '【封禁代理】-流量已用完');
		}
	}

	// 解封被临时封禁的账号
	private function unblockUsers(): void {
		// 解封被临时封禁的账号
		$userList = User::query()->whereEnable(0)->where('status', '>=', 0)->where('ban_time', '>', 0)->get();
		foreach($userList as $user){
			if($user->ban_time < time()){
				User::query()->whereId($user->id)->update(['enable' => 1, 'ban_time' => 0]);

				// 写入操作日志
				$this->addUserBanLog($user->id, 0, '【自动解封】-临时封禁到期');
			}
		}

		// 可用流量大于已用流量也解封（比如：邀请返利自动加了流量）
		$userList = User::query()
		                ->whereEnable(0)
		                ->where('status', '>=', 0)
		                ->whereBanTime(0)
		                ->where('expire_time', '>=', date('Y-m-d'))
		                ->whereRaw("u + d < transfer_enable")
		                ->get();
		foreach($userList as $user){
			User::query()->whereId($user->id)->update(['enable' => 1]);

			// 写入操作日志
			$this->addUserBanLog($user->id, 0, '【自动解封】-有流量解封');
		}
	}

	// 端口回收与分配
	private function dispatchPort(): void {
		if(self::$systemConfig['auto_release_port']){
			## 自动分配端口
			$userList = User::query()->whereEnable(1)->where('status', '>=', 0)->wherePort(0)->get();
			foreach($userList as $user){
				$port = self::$systemConfig['is_rand_port']? Helpers::getRandPort() : Helpers::getOnlyPort();

				User::query()->whereId($user->id)->update(['port' => $port]);
			}

			## 被封禁的账号自动释放端口
			User::query()->whereEnable(0)->whereStatus(-1)->where('port', '!=', 0)->update(['port' => 0]);

			## 过期一个月的账户自动释放端口
			User::query()
			    ->whereEnable(0)
			    ->where('port', '!=', 0)
			    ->where('expire_time', '<=', date("Y-m-d", strtotime("-30 days")))
			    ->update(['port' => 0]);
		}
	}

	// 检测节点是否离线
	private function checkNodeStatus(): void {
		if(self::$systemConfig['is_node_offline']){
			$nodeList = SsNode::whereIsRelay(0)->whereStatus(1)->get();
			foreach($nodeList as $node){
				// 10分钟内无节点负载信息则认为是后端炸了
				$nodeTTL = SsNodeInfo::query()
				                     ->whereNodeId($node->id)
				                     ->where('log_time', '>=', strtotime("-10 minutes"))
				                     ->orderByDesc('id')
				                     ->doesntExist();
				if($nodeTTL && self::$systemConfig['offline_check_times']){
					// 已通知次数
					$cacheKey = 'offline_check_times'.$node->id;
					if(Cache::has($cacheKey)){
						$times = Cache::get($cacheKey);
					}else{
						// 键将保留24小时
						Cache::put($cacheKey, 1, Day);
						$times = 1;
					}

					if($times < self::$systemConfig['offline_check_times']){
						Cache::increment($cacheKey);
						PushNotification::send('节点异常警告', "节点**{$node->name}【{$node->ip}】**异常：**心跳异常，可能离线了**");
					}
				}
			}
		}
	}
}
