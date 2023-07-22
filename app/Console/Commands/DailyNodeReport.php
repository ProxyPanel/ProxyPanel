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
            $nodeDailyLogs = NodeDailyDataFlow::with('node:id,name')->has('node')->orderBy('node_id')->whereDate('created_at', date('Y-m-d', strtotime('yesterday')))->get();

            $data = [];
            foreach ($nodeDailyLogs as $log) {
                $data[] = [
                    'name' => $log->node->name,
                    'upload' => formatBytes($log->u),
                    'download' => formatBytes($log->d),
                    'total' => formatBytes($log->u + $log->d),
                ];
            }

            if ($data) {
                $u = $nodeDailyLogs->sum('u');
                $d = $nodeDailyLogs->sum('d');
                $data[] = [
                    'name' => trans('notification.node.total'),
                    'upload' => formatBytes($u),
                    'download' => formatBytes($d),
                    'total' => formatBytes($u + $d),
                ];

                Notification::send(User::role('Super Admin')->get(), new NodeDailyReport($data));
            }
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }
}
