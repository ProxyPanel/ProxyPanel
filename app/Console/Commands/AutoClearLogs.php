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
    protected $signature = 'autoClearLogs {--light : 只清理保留期最短的节点心跳（供高频调度使用）}';

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
            // 节点负载信息保留期最短（默认 30 分钟），放在最前面，供 --light 高频清理
            $this->deleteInBatches(NodeHeartbeat::where('log_time', '<=', strtotime(sysConfig('tasks_clean.node_heartbeats')))); // 清除节点负载信息日志

            if ($this->option('light')) {
                return;
            }

            $this->deleteInBatches(NodeDailyDataFlow::whereNotNull('node_id')->where('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.node_daily_logs'))))); // 清除节点每天流量数据日志

            $this->deleteInBatches(NodeHourlyDataFlow::where('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.node_hourly_logs'))))); // 清除节点每小时流量数据日志

            $this->deleteInBatches(NodeOnlineLog::where('log_time', '<=', strtotime(sysConfig('tasks_clean.node_online_logs')))); // 清除节点在线用户数日志

            $this->deleteInBatches(RuleLog::where('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.rule_logs'))))); // 清理审计触发日志

            $this->deleteInBatches(NodeOnlineIp::where('created_at', '<=', strtotime(sysConfig('tasks_clean.node_online_ips')))); // 清除用户连接IP

            // 清除用户各节点 / 节点总计的每天流量数据日志
            $this->deleteInBatches(UserDailyDataFlow::where(static function (Builder $query) {
                $query->where('node_id', '<>', null)->where('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.user_daily_logs_nodes'))));
            })->orWhere('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.user_daily_logs_total')))));

            $this->deleteInBatches(UserHourlyDataFlow::where('created_at', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.user_hourly_logs'))))); // 清除用户每时各流量数据日志

            $this->deleteInBatches(UserSubscribeLog::where('request_time', '<=', date('Y-m-d H:i:s', strtotime(sysConfig('tasks_clean.subscribe_logs'))))); // 清理用户订阅请求日志

            $this->deleteInBatches(UserDataFlowLog::where('log_time', '<=', strtotime(sysConfig('tasks_clean.traffic_logs')))); // 清除用户流量日志
        } catch (Exception $e) {
            Log::emergency(trans('common.error_item', ['attribute' => trans('model.config.is_clear_log')]).': '.$e->getMessage());
        }
    }

    /**
     * 分批删除。
     *
     * 单条无限量 DELETE 会长时间持有行锁并把 undo/redo 撑成一个大事务，是清理任务阻塞其它查询的主因；
     * 分批后每批独立提交，锁与事务都显著变小。过滤列均已建索引（见 add_log_cleanup_indexes 迁移），
     * 所以每批都是索引区间扫描而不是全表扫描。步长沿用仓库既有的 tasks_chunk 约定。
     */
    private function deleteInBatches(Builder $query): void
    {
        $chunkSize = max(1, (int) sysConfig('tasks_chunk', 3000));

        do {
            $rows = (clone $query)->limit($chunkSize)->delete();
        } while ($rows >= $chunkSize);
    }
}
