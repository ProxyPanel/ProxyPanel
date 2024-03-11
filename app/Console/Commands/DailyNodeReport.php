<?php

namespace App\Console\Commands;

use App\Models\NodeDailyDataFlow;
use App\Models\User;
use App\Notifications\NodeDailyReport;
use Illuminate\Console\Command;
use Log;
use Notification;

class DailyNodeReport extends Command
{
    protected $signature = 'dailyNodeReport';

    protected $description = '自动报告节点昨日使用情况';

    public function handle(): void
    {
        $jobTime = microtime(true);

        if (sysConfig('node_daily_notification')) {
            $nodeDailyLogs = NodeDailyDataFlow::with('node:id,name')->has('node')->whereDate('created_at', date('Y-m-d', strtotime('yesterday')))->orderBy('node_id')->get();

            $data = [];
            $sum_u = 0;
            $sum_d = 0;
            foreach ($nodeDailyLogs as $log) {
                $data[] = [
                    'name' => $log->node->name,
                    'upload' => formatBytes($log->u),
                    'download' => formatBytes($log->d),
                    'total' => formatBytes($log->u + $log->d),
                ];
                $sum_u += $log->u;
                $sum_d += $log->d;
            }

            if ($data) {
                $data[] = [
                    'name' => trans('notification.node.total'),
                    'upload' => formatBytes($sum_u),
                    'download' => formatBytes($sum_d),
                    'total' => formatBytes($sum_u + $sum_d),
                ];

                $superAdmins = User::role('Super Admin')->get();
                if ($superAdmins->isNotEmpty()) {
                    Notification::send($superAdmins, new NodeDailyReport($data));
                }
            }
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }
}
