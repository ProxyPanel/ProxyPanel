<?php

namespace App\Console\Commands;

use App\Models\NodeDailyDataFlow;
use App\Models\NodeHeartBeat;
use App\Models\NodeHourlyDataFlow;
use App\Models\NodeOnlineLog;
use App\Models\NodeOnlineUserIp;
use App\Models\UserBanedLog;
use App\Models\UserDailyDataFlow;
use App\Models\UserDataFlowLog;
use App\Models\UserHourlyDataFlow;
use App\Models\UserLoginLog;
use App\Models\UserSubscribeLog;
use Exception;
use Illuminate\Console\Command;
use Log;

class AutoClearLog extends Command
{
    protected $signature = 'autoClearLog';
    protected $description = '自动清除日志';

    public function handle(): void
    {
        $jobStartTime = microtime(true);

        // 清除日志
        if (sysConfig('is_clear_log')) {
            $this->clearLog();
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    // 清除日志
    private function clearLog(): void
    {
        try {
            // 清除节点负载信息日志
            NodeHeartBeat::where('log_time', '<=', strtotime("-30 minutes"))->delete();

            // 清除节点在线用户数日志
            NodeOnlineLog::where('log_time', '<=', strtotime("-1 hour"))->delete();

            // 清除用户流量日志
            UserDataFlowLog::where('log_time', '<=', strtotime("-3 days"))->delete();

            // 清除用户每时各流量数据日志
            UserHourlyDataFlow::where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-3 days')))->delete();

            // 清除用户各节点 / 节点总计的每天流量数据日志
            UserDailyDataFlow::where('node_id', '<>', 0)
                ->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-1 month')))
                ->orWhere('created_at', '<=', date('Y-m-d H:i:s', strtotime('-3 month')))
                ->delete();

            // 清除节点每小时流量数据日志
            NodeHourlyDataFlow::where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-3 days')))->delete();

            // 清除节点每天流量数据日志
            NodeDailyDataFlow::where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-2 month')))->delete();

            // 清除用户封禁日志
            UserBanedLog::where('created_at', '<=', date('Y-m-d H:i:s', strtotime("-3 month")))->delete();

            // 清除用户连接IP
            NodeOnlineUserIp::where('created_at', '<=', strtotime("-1 month"))->delete();

            // 清除用户登陆日志
            UserLoginLog::where('created_at', '<=', date('Y-m-d H:i:s', strtotime("-3 month")))->delete();

            // 清除用户订阅记录
            UserSubscribeLog::where('request_time', '<=', date('Y-m-d H:i:s', strtotime("-1 month")))->delete();
        } catch (Exception $e) {
            Log::error('【清理日志】错误： '.$e->getMessage());
        }
    }
}
