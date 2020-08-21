<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\PushNotification;
use App\Models\Config;
use App\Models\Coupon;
use App\Models\Invite;
use App\Models\Node;
use App\Models\NodeHeartBeat;
use App\Models\Order;
use App\Models\User;
use App\Models\UserBanedLog;
use App\Models\UserHourlyDataFlow;
use App\Models\UserSubscribeLog;
use App\Models\VerifyCode;
use Cache;
use Illuminate\Console\Command;
use Log;

class AutoJob extends Command {
	protected $signature = 'autoJob';
	protected $description = '自动化任务';

	/*
	 * 警告：除非熟悉业务流程，否则不推荐更改以下执行顺序，随意变更以下顺序可能导致系统异常
	 */
	public function handle(): void {
		$jobStartTime = microtime(true);

		// 关闭超时未支付本地订单
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
		if(sysConfig('auto_release_port')){
			$this->dispatchPort();
		}

		// 检测节点是否离线
		$this->checkNodeStatus();

		// 检查维护模式
		if(sysConfig('maintenance_mode')){
			Config::whereIn('name', ['maintenance_mode', 'maintenance_time'])->update(['value' => null]);
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	// 关闭超时未支付本地订单
	private function closeOrders(): void {
		// 关闭超时未支付的本地支付订单
		foreach(Order::recentUnPay()->get() as $order){
			// 关闭订单
			$order->update(['status' => -1]);
		}
	}

	// 注册验证码自动置无效 & 优惠券无效化
	private function expireCode(): void {
		// 注册验证码自动置无效
		VerifyCode::recentUnused()->update(['status' => 2]);

		// 优惠券到期自动置无效
		Coupon::whereStatus(0)->where('end_time', '<=', time())->update(['status' => 2]);

		// 用尽的优惠劵
		Coupon::whereStatus(0)->whereIn('type', [1, 2])->whereUsableTimes(0)->update(['status' => 2]);

		// 邀请码到期自动置无效
		Invite::whereStatus(0)->where('dateline', '<=', date('Y-m-d H:i:s'))->update(['status' => 2]);
	}

	// 封禁访问异常的订阅链接
	private function blockSubscribe(): void {
		if(sysConfig('is_subscribe_ban')){
			$pastSubLogs = UserSubscribeLog::where('request_time', '>=', date("Y-m-d H:i:s", strtotime("-1 days")))
			                               ->groupBy('user_subscribe_id')
			                               ->selectRaw('count(*) as total, user_subscribe_id')
			                               ->get();
			foreach($pastSubLogs as $log){
				if($log->total >= sysConfig('subscribe_ban_times')){
					$subscribe = $log->subscribe;
					$ret = $subscribe->update([
						'status'   => 0,
						'ban_time' => time(),
						'ban_desc' => '存在异常，自动封禁'
					]);

					// 记录封禁日志
					if($ret){
						$this->addUserBanLog($subscribe->user_id, 0, '【完全封禁订阅】-订阅24小时内请求异常');
					}else{
						Log::error('【自动化任务】封禁订阅失败，尝试封禁订阅ID：'.$subscribe->id);
					}
				}
			}
		}
	}

	/**
	 * 添加用户封禁日志
	 *
	 * @param  int     $userId       用户ID
	 * @param  int     $time         封禁时长，单位分钟
	 * @param  string  $description  封禁理由
	 */
	private function addUserBanLog($userId, $time, $description): void {
		$log = new UserBanedLog();
		$log->user_id = $userId;
		$log->time = $time;
		$log->description = $description;
		$log->save();
	}

	// 封禁账号
	private function blockUsers(): void {
		// 封禁1小时内流量异常账号
		if(sysConfig('is_traffic_ban')){
			$userList = User::activeUser()->whereBanTime(0)->get();
			foreach($userList as $user){
				// 对管理员豁免
				if($user->is_admin){
					continue;
				}

				// 多往前取5分钟，防止数据统计任务执行时间过长导致没有数据
				$totalTraffic = UserHourlyDataFlow::userRecentUsed($user->id)->sum('total');
				if($totalTraffic >= sysConfig('traffic_ban_value') * GB){
					$user->update([
						'enable'   => 0,
						'ban_time' => strtotime("+".sysConfig('traffic_ban_time')." minutes")
					]);

					// 写入日志
					$this->addUserBanLog($user->id, sysConfig('traffic_ban_time'), '【临时封禁代理】-1小时内流量异常');
				}
			}
		}

		// 禁用流量超限用户
		$userList = User::activeUser()->whereBanTime(0)->whereRaw("u + d >= transfer_enable")->get();
		foreach($userList as $user){
			$user->update(['enable' => 0]);

			// 写入日志
			$this->addUserBanLog($user->id, 0, '【封禁代理】-流量已用完');
		}
	}

	// 解封被临时封禁的账号
	private function unblockUsers(): void {
		// 解封被临时封禁的账号
		$userList = User::whereEnable(0)->where('status', '>=', 0)->where('ban_time', '>', 0)->get();
		foreach($userList as $user){
			if($user->ban_time < time()){
				$user->update(['enable' => 1, 'ban_time' => 0]);

				// 写入操作日志
				$this->addUserBanLog($user->id, 0, '【自动解封】-临时封禁到期');
			}
		}

		// 可用流量大于已用流量也解封（比如：邀请返利自动加了流量）
		$userList = User::whereEnable(0)
		                ->where('status', '>=', 0)
		                ->whereBanTime(0)
		                ->where('expired_at', '>=', date('Y-m-d'))
		                ->whereRaw("u + d < transfer_enable")
		                ->get();
		foreach($userList as $user){
			$user->update(['enable' => 1]);

			// 写入操作日志
			$this->addUserBanLog($user->id, 0, '【自动解封】-有流量解封');
		}
	}

	// 端口回收与分配
	private function dispatchPort(): void {
		## 自动分配端口
		foreach(User::activeUser()->wherePort(0)->get() as $user){
			$port = sysConfig('is_rand_port')? Helpers::getRandPort() : Helpers::getOnlyPort();

			$user->update(['port' => $port]);
		}

		## 被封禁的账号自动释放端口
		User::whereEnable(0)->whereStatus(-1)->where('port', '!=', 0)->update(['port' => 0]);

		## 过期一个月的账户自动释放端口
		User::whereEnable(0)
		    ->where('port', '!=', 0)
		    ->where('expired_at', '<=', date("Y-m-d", strtotime("-1 months")))
		    ->update(['port' => 0]);
	}

	// 检测节点是否离线
	private function checkNodeStatus(): void {
		if(sysConfig('is_node_offline')){
			$onlineNode = NodeHeartBeat::recently()->distinct()->pluck('node_id')->toArray();
			foreach(Node::whereIsRelay(0)->whereStatus(1)->get() as $node){
				// 10分钟内无节点负载信息则认为是后端炸了
				$nodeTTL = !in_array($node->id, $onlineNode);
				if($nodeTTL && sysConfig('offline_check_times')){
					// 已通知次数
					$cacheKey = 'offline_check_times'.$node->id;
					if(Cache::has($cacheKey)){
						$times = Cache::get($cacheKey);
					}else{
						// 键将保留24小时
						Cache::put($cacheKey, 1, Day);
						$times = 1;
					}

					if($times < sysConfig('offline_check_times')){
						Cache::increment($cacheKey);
						PushNotification::send('节点异常警告', "节点**{$node->name}【{$node->ip}】**异常：**心跳异常，可能离线了**");
					}
				}
			}
		}
	}
}
