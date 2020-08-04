<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Models\NodeInfo;
use App\Models\NodeOnlineLog;
use App\Models\NodeOnlineUserIp;
use App\Models\NodeDailyDataFlow;
use App\Models\NodeHourlyDataFlow;
use App\Models\UserBanedLog;
use App\Models\UserDailyDataFlow;
use App\Models\UserDataFlowLog;
use App\Models\UserHourlyDataFlow;
use App\Models\UserLoginLog;
use App\Models\UserSubscribeLog;
use Exception;
use Illuminate\Console\Command;
use Log;

class AutoClearLog extends Command {
	protected $signature = 'autoClearLog';
	protected $description = '自动清除日志';

	public function handle(): void {
		$jobStartTime = microtime(true);

		// 清除日志
		if(Helpers::systemConfig()['is_clear_log']){
			$this->clearLog();
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	// 清除日志
	private function clearLog(): void {
		try{
			// 清除节点负载信息日志
			NodeInfo::query()->where('log_time', '<=', strtotime("-30 minutes"))->delete();

			// 清除节点在线用户数日志
			NodeOnlineLog::query()->where('log_time', '<=', strtotime("-1 hour"))->delete();

			// 清除用户流量日志
			UserDataFlowLog::query()->where('log_time', '<=', strtotime("-3 days"))->delete();

			// 清除用户每时各流量数据日志
			UserHourlyDataFlow::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-3 days')))->delete();

			// 清除用户各节点的每天流量数据日志
			UserDailyDataFlow::query()
			                 ->where('node_id', '<>', 0)
			                 ->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-1 month')))
			                 ->delete();

			// 清除用户每天流量数据日志
			UserDailyDataFlow::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-3 month')))->delete();

			// 清除节点每小时流量数据日志
			NodeHourlyDataFlow::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-3 days')))->delete();

			// 清除节点每天流量数据日志
			NodeDailyDataFlow::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-2 month')))->delete();

			// 清除用户封禁日志
			UserBanedLog::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime("-3 month")))->delete();

			// 清除用户连接IP
			NodeOnlineUserIp::query()->where('created_at', '<=', strtotime("-1 month"))->delete();

			// 清除用户登陆日志
			UserLoginLog::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime("-3 month")))->delete();

			// 清除用户订阅记录
			UserSubscribeLog::query()
			                ->where('request_time', '<=', date('Y-m-d H:i:s', strtotime("-1 month")))
			                ->delete();
		}catch(Exception $e){
			Log::error('【清理日志】错误： '.$e->getMessage());
		}

	}

}
