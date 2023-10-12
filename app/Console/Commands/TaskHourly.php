<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Models\User;
use App\Notifications\DataAnomaly;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Log;
use Notification;

class TaskHourly extends Command
{
    protected $signature = 'task:hourly';

    protected $description = '每小时任务';

    public function handle(): void
    {
        $jobTime = microtime(true);

        $this->userTrafficStatistics(); // 用户小时流量统计
        $this->nodeTrafficStatistics(); // 节点小时流量统计

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }

    private function userTrafficStatistics(): void
    {
        $created_at = date('Y-m-d H:59:59', strtotime('-1 hour'));
        $end = strtotime($created_at);
        $start = $end - 3599;
        $data_anomaly_notification = sysConfig('data_anomaly_notification');
        $traffic_ban_value = (int) sysConfig('traffic_ban_value') * GiB;
        User::activeUser()->with('dataFlowLogs')->WhereHas('dataFlowLogs')->whereHas('dataFlowLogs', function (Builder $query) use ($start, $end) {
            $query->whereBetween('log_time', [$start, $end]);
        })->chunk(config('tasks.chunk'), function ($users) use ($traffic_ban_value, $start, $end, $created_at, $data_anomaly_notification) {
            foreach ($users as $user) {
                $logs = $user->dataFlowLogs()
                    ->whereBetween('log_time', [$start, $end])
                    ->groupBy('node_id')
                    ->selectRaw('node_id, sum(`u`) as u, sum(`d`) as d')
                    ->get();

                $data = $logs->each(function ($log) use ($created_at) {
                    $log->created_at = $created_at;
                })->toArray();
                $overall = [ // 每小时节点流量合计
                    'node_id' => null,
                    'u' => $logs->sum('u'),
                    'd' => $logs->sum('d'),
                    'created_at' => $created_at,
                ];
                $data[] = $overall;
                $user->hourlyDataFlows()->createMany($data);
                $overall['total'] = $overall['u'] + $overall['d'];

                // 用户流量异常警告
                if ($data_anomaly_notification && $overall['total'] >= $traffic_ban_value) {
                    Notification::send(User::find(1), new DataAnomaly($user->id, formatBytes($overall['u']), formatBytes($overall['d']), formatBytes($overall['total'])));
                }
            }
        });
    }

    private function nodeTrafficStatistics(): void
    {
        $created_at = date('Y-m-d H:59:59', strtotime('-1 hour'));
        $end = strtotime($created_at);
        $start = $end - 3599;

        Node::orderBy('id')->with('userDataFlowLogs')->whereHas('userDataFlowLogs', function (Builder $query) use ($start, $end) {
            $query->whereBetween('log_time', [$start, $end]);
        })->chunk(config('tasks.chunk'), function ($nodes) use ($start, $end, $created_at) {
            foreach ($nodes as $node) {
                $traffic = $node->userDataFlowLogs()->whereBetween('log_time', [$start, $end])->selectRaw('sum(`u`) as u, sum(`d`) as d')->first();
                $node->hourlyDataFlows()->create(['u' => $traffic->u, 'd' => $traffic->d, 'created_at' => $created_at]);
            }
        });
    }
}
