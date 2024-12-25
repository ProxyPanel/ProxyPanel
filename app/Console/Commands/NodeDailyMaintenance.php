<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Models\NodeDailyDataFlow;
use App\Models\User;
use App\Notifications\NodeDailyReport;
use App\Notifications\NodeRenewal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Log;
use Notification;

class NodeDailyMaintenance extends Command
{
    protected $signature = 'node:maintenance';

    protected $description = '执行节点的日常维护，包括发送每日使用报告和检查续约提醒';

    public function handle(): void
    {
        $jobTime = microtime(true);

        if (sysConfig('node_daily_notification')) {
            $this->nodedailyReport();
        }

        if (sysConfig('node_renewal_notification')) {// 通知节点急需续约
            $this->checkNodeRenewDays();
        }

        $this->updateNodeRenewal();

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    private function nodedailyReport(): void
    {
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

    private function checkNodeRenewDays(): void
    {
        $now = Carbon::now();

        $notificationDates = [ // 通知日期 分别是 前1天，3天和7天
            $now->addDays(1)->format('Y-m-d'),
            $now->addDays(2)->format('Y-m-d'),
            $now->addDays(4)->format('Y-m-d'),
        ];

        $nodes = Node::whereNotNull('details')->whereIn('details->next_renewal_date', $notificationDates)->pluck('name', 'id')->toArray();

        if (! empty($nodes)) {
            Notification::send(User::find(1), new NodeRenewal($nodes));
        }
    }

    private function updateNodeRenewal(): void
    {
        // 获取符合条件的节点
        $nodes = Node::whereNotNull('details')
            ->where(function (Builder $query) {
                $query->where('details->subscription_term', '<>', null)
                    ->where('details->next_renewal_date', '<=', Carbon::now()->format('Y-m-d'));
            })
            ->get();

        // 更新每个节点的 next_renewal_date
        foreach ($nodes as $node) {
            $details = $node->details;
            $details['next_renewal_date'] = Carbon::createFromFormat('Y-m-d', $details['next_renewal_date'])->add($details['subscription_term'])->format('Y-m-d');
            $node->details = $details;

            $node->save();
        }
    }
}
