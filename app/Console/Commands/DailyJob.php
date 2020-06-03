<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\PushNotification;
use App\Models\Invite;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserBanLog;
use Illuminate\Console\Command;
use Log;

class DailyJob extends Command {
	protected static $systemConfig;
	protected $signature = 'dailyJob';
	protected $description = '每日任务';

	public function __construct() {
		parent::__construct();
		self::$systemConfig = Helpers::systemConfig();
	}

	public function handle() {
		$jobStartTime = microtime(true);

		// 过期用户处理
		$this->expireUser();

		// 关闭超过72小时未处理的工单
		$this->closeTickets();

		// 重置用户流量
		if(self::$systemConfig['reset_traffic']){
			$this->resetUserTraffic();
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	private function expireUser() {
		// 过期用户处理
		$userList = User::query()
		                ->where('status', '>=', 0)
		                ->whereEnable(1)
		                ->where('expire_time', '<', date('Y-m-d'))
		                ->get();
		foreach($userList as $user){
			if(self::$systemConfig['is_ban_status']){
				User::query()->whereId($user->id)->update([
					'u'               => 0,
					'd'               => 0,
					'transfer_enable' => 0,
					'enable'          => 0,
					'reset_time'      => null,
					'ban_time'        => 0,
					'status'          => -1
				]);

				$this->addUserBanLog($user->id, 0, '【禁止登录，清空账户】-账号已过期');

				// 废除其名下邀请码
				Invite::query()->whereUid($user->id)->whereStatus(0)->update(['status' => 2]);

				// 写入用户流量变动记录
				Helpers::addUserTrafficModifyLog($user->id, 0, $user->transfer_enable, 0, '[定时任务]账号已过期(禁止登录，清空账户)');
			}else{
				User::query()->whereId($user->id)->update([
					'u'               => 0,
					'd'               => 0,
					'transfer_enable' => 0,
					'enable'          => 0,
					'reset_time'      => null,
					'ban_time'        => 0
				]);

				$this->addUserBanLog($user->id, 0, '【封禁代理，清空账户】-账号已过期');

				// 写入用户流量变动记录
				Helpers::addUserTrafficModifyLog($user->id, 0, $user->transfer_enable, 0, '[定时任务]账号已过期(封禁代理，清空账户)');
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
	private function addUserBanLog($userId, $minutes, $description) {
		$log = new UserBanLog();
		$log->user_id = $userId;
		$log->minutes = $minutes;
		$log->description = $description;
		$log->save();
	}

	// 关闭超过72小时未处理的工单
	private function closeTickets() {
		$ticketList = Ticket::query()
		                    ->where('updated_at', '<=', date('Y-m-d', strtotime("-3 days")))
		                    ->whereStatus(1)
		                    ->get();
		foreach($ticketList as $ticket){
			$ret = Ticket::query()->whereId($ticket->id)->update(['status' => 2]);
			if($ret){
				PushNotification::send('工单关闭提醒', '工单：ID'.$ticket->id.'超过72小时未处理，系统已自动关闭');
			}
		}
	}

	// 重置用户流量
	private function resetUserTraffic() {
		$userList = User::query()
		                ->where('status', '>=', 0)
		                ->where('expire_time', '>', date('Y-m-d'))
		                ->where('reset_time', '<=', date('Y-m-d'))
		                ->get();
		foreach($userList as $user){
			// 跳过 没有重置日期的账号
			if(!$user->reset_time){
				continue;
			}

			// 取出用户正在使用的套餐
			$order = Order::query()
			              ->with(['goods'])
			              ->whereUserId($user->id)
			              ->whereStatus(2)
			              ->whereIsExpire(0)
			              ->whereHas('goods', function($q) {
				              $q->whereType(2);
			              })
			              ->first();

			// 无订单的免费/特殊用户跳过
			if(!$order){
				continue;
			}

			// 过期生效中的加油包
			Order::query()
			     ->with(['goods'])
			     ->whereUserId($user->id)
			     ->whereStatus(2)
			     ->whereIsExpire(0)
			     ->whereHas('goods', function($q) {
				     $q->whereType(1);
			     })
			     ->update(['is_expire' => 1]);

			//账号下一个重置时间
			$nextResetTime = date('Y-m-d', strtotime("+".$order->goods->period." days"));
			if($nextResetTime >= $user->expire_time){
				$nextResetTime = null;
			}
			// 可用流量 变动日志
			if($user->transfer_enable != $order->goods->traffic * 1048576){
				Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $user->transfer_enable,
					$order->goods->traffic * 1048576, '【流量重置】重置可用流量');
			}
			// 重置流量
			User::query()->whereId($user->id)->update([
				'u'               => 0,
				'd'               => 0,
				'transfer_enable' => $order->goods->traffic * 1048576,
				'reset_time'      => $nextResetTime
			]);
			Log::info('用户[ID：'.$user->id.'  昵称： '.$user->username.'  邮箱： '.$user->email.'] 流量重置为 '.($order->goods->traffic * 1048576).'. 重置日期为 '.($nextResetTime?: '【无】'));
		}
	}
}
