<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\PushNotification;
use App\Http\Models\User;
use App\Http\Models\UserTrafficHourly;
use Illuminate\Console\Command;
use Log;

class UserTrafficAbnormalAutoWarning extends Command {
	protected static $systemConfig;
	protected $signature = 'userTrafficAbnormalAutoWarning';
	protected $description = '用户流量异常警告';

	public function __construct() {
		parent::__construct();
		self::$systemConfig = Helpers::systemConfig();
	}

	public function handle() {
		$jobStartTime = microtime(true);

		// 用户流量异常警告
		$this->userTrafficAbnormalWarning();

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	// 用户流量异常警告
	private function userTrafficAbnormalWarning() {
		// 1小时内流量异常用户(多往前取5分钟，防止数据统计任务执行时间过长导致没有数据)
		$userTotalTrafficList = UserTrafficHourly::query()
		                                         ->whereNodeId(0)
		                                         ->where('total', '>', 104857600)
		                                         ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))
		                                         ->groupBy('user_id')
		                                         ->selectRaw("user_id, sum(total) as totalTraffic")
		                                         ->get(); // 只统计100M以上的记录，加快查询速度
		if(!$userTotalTrafficList->isEmpty()){
			$title = "流量异常用户提醒";

			foreach($userTotalTrafficList as $vo){
				$user = User::query()->whereId($vo->user_id)->first();

				// 推送通知管理员
				if($vo->totalTraffic > (self::$systemConfig['traffic_ban_value'] * 1073741824)){
					$traffic = UserTrafficHourly::query()
					                            ->whereNodeId(0)
					                            ->whereUserId($vo->user_id)
					                            ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))
					                            ->selectRaw("user_id, sum(`u`) as totalU, sum(`d`) as totalD, sum(total) as totalTraffic")
					                            ->first();

					$content = "用户**{$user->email}(ID:{$user->id})**，最近1小时**上行流量：".flowAutoShow($traffic->totalU)."，下行流量：".flowAutoShow($traffic->totalD)."，共计：".flowAutoShow($traffic->totalTraffic)."**。";

					PushNotification::send($title, $content);
				}
			}
		}
	}
}
