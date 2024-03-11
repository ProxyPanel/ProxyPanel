<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Models\User;
use App\Notifications\DataAnomaly;
use DB;
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
        User::activeUser()->whereHas('dataFlowLogs', function (Builder $query) use ($start, $end) {
            $query->whereBetween('log_time', [$start, $end]);
        })->with([
            'dataFlowLogs' => function ($query) use ($start, $end) {
                $query->whereBetween('log_time', [$start, $end]);
            },
        ])->chunk(config('tasks.chunk'), function ($users) use ($traffic_ban_value, $created_at, $data_anomaly_notification) {
            foreach ($users as $user) {
                $dataFlowLogs = $user->dataFlowLogs->groupBy('node_id');

                $data = $dataFlowLogs->map(function ($logs, $nodeId) use ($created_at) {
                    $totals = $logs->reduce(function ($carry, $log) {
                        $carry['u'] += $log['u'];
                        $carry['d'] += $log['d'];

                        return $carry;
                    }, ['u' => 0, 'd' => 0]);

                    return [
                        'node_id' => $nodeId,
                        'u' => $totals['u'],
                        'd' => $totals['d'],
                        'created_at' => $created_at,
                    ];
                })->values()->all();

                $sum_u = array_sum(array_column($data, 'u'));
                $sum_d = array_sum(array_column($data, 'd'));
                $data[] = [ // 每小时节点流量合计
                    'node_id' => null,
                    'u' => $sum_u,
                    'd' => $sum_d,
                    'created_at' => $created_at,
                ];

                $user->hourlyDataFlows()->createMany($data);
                $sum_all = $sum_u + $sum_d;

                // 用户流量异常警告
                if ($data_anomaly_notification && $sum_all >= $traffic_ban_value) {
                    Notification::send(User::find(1), new DataAnomaly($user->id, formatBytes($sum_u), formatBytes($sum_d), formatBytes($sum_all)));
                }
            }
        });
    }

    private function nodeTrafficStatistics(): void
    {
        $created_at = date('Y-m-d H:59:59', strtotime('-1 hour'));
        $end = strtotime($created_at);
        $start = $end - 3599;

        Node::whereHas('userDataFlowLogs', function (Builder $query) use ($start, $end) {
            $query->whereBetween('log_time', [$start, $end]);
        })->withCount([
            'userDataFlowLogs as u_sum' => function ($query) use ($start, $end) {
                $query->select(DB::raw('SUM(u)'))->whereBetween('log_time', [$start, $end]);
            },
        ])->withCount([
            'userDataFlowLogs as d_sum' => function ($query) use ($start, $end) {
                $query->select(DB::raw('SUM(d)'))->whereBetween('log_time', [$start, $end]);
            },
        ])->chunk(config('tasks.chunk'), function ($nodes) use ($created_at) {
            foreach ($nodes as $node) {
                $node->hourlyDataFlows()->create(['u' => $node->u_sum, 'd' => $node->d_sum, 'created_at' => $created_at]);
            }
        });
    }
}
