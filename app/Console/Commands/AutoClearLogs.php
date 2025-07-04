<?php

namespace App\Console\Commands;

use App\Models\NodeDailyDataFlow;
use App\Models\NodeHeartbeat;
use App\Models\NodeHourlyDataFlow;
use App\Models\NodeOnlineIp;
use App\Models\NodeOnlineLog;
use App\Models\RuleLog;
use App\Models\UserDailyDataFlow;
use App\Models\UserDataFlowLog;
use App\Models\UserHourlyDataFlow;
use App\Models\UserSubscribeLog;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Log;

class AutoClearLogs extends Command
{
    protected $signature = 'autoClearLogs';

    protected $description = '自动清除日志';

    public function handle(): void
    {
        $jobTime = microtime(true);

        if (sysConfig('is_clear_log')) {
            $this->clearLog(); // 清除日志
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    // 清除日志
    private function clearLog(): void
    {
        try {
            NodeDailyDataFlow::whereNotNull('node_id')->where('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.node_daily_logs'))))->delete(); // 清除节点每天流量数据日志

            NodeHourlyDataFlow::where('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.node_hourly_logs'))))->delete(); // 清除节点每小时流量数据日志

            NodeHeartbeat::where('log_time', '<=', strtotime(sysConfig('tasks_clean.node_heartbeats')))->delete(); // 清除节点负载信息日志

            NodeOnlineLog::where('log_time', '<=', strtotime(sysConfig('tasks_clean.node_online_logs')))->delete(); // 清除节点在线用户数日志

            RuleLog::where('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.rule_logs'))))->delete(); // 清理审计触发日志

            NodeOnlineIp::where('created_at', '<=', strtotime(sysConfig('tasks_clean.node_online_ips')))->delete(); // 清除用户连接IP

            UserDailyDataFlow::where(static function (Builder $query) {
                $query->where('node_id', '<>', null)->where('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.user_daily_logs_nodes'))));
            })->orWhere('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.user_daily_logs_total'))))->delete(); // 清除用户各节点 / 节点总计的每天流量数据日志

            UserHourlyDataFlow::where('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.user_hourly_logs'))))->delete(); // 清除用户每时各流量数据日志

            UserSubscribeLog::where('request_time', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.subscribe_logs'))))->delete(); // 清理用户订阅请求日志

            UserDataFlowLog::where('log_time', '<=', strtotime(sysConfig('tasks_clean.traffic_logs')))->delete(); // 清除用户流量日志
        } catch (Exception $e) {
            Log::emergency(trans('common.error_item', ['attribute' => trans('model.config.is_clear_log')]).': '.$e->getMessage());
        }
    }
}
