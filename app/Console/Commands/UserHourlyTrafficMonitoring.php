<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DataAnomaly;
use Illuminate\Console\Command;
use Log;
use Notification;

class UserHourlyTrafficMonitoring extends Command
{
    protected $signature = 'userHourlyTrafficMonitoring';
    protected $description = '用户每小时流量监控';
    private $data_anomaly_notification;
    private $traffic_ban_value;

    public function handle()
    {
        $jobStartTime = microtime(true);
        $this->data_anomaly_notification = sysConfig('data_anomaly_notification');
        $this->traffic_ban_value = sysConfig('traffic_ban_value') * GB;

        User::activeUser()->with('dataFlowLogs')->WhereHas('dataFlowLogs')->chunk(config('tasks.chunk'), function ($users) {
            foreach ($users as $user) {
                $this->statisticsByUser($user);
            }
        });

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    private function statisticsByUser(User $user)
    {
        $logs = $user->dataFlowLogs()
            ->whereBetween('log_time', [strtotime('-1 hour'), time()])
            ->groupBy('node_id')
            ->selectRaw('node_id, sum(`u`) as u, sum(`d`) as d')
            ->get();

        if ($logs->isNotEmpty()) { // 有数据才记录
            $data = $logs->each(function ($log) {
                $log->total = $log->u + $log->d;
                $log->traffic = flowAutoShow($log->total);
            })->flatten()->toArray();

            $data[] = [ // 每小时节点流量合计
                'u'       => $logs->sum('u'),
                'd'       => $logs->sum('d'),
                'total'   => $logs->sum('total'),
                'traffic' => flowAutoShow($logs->sum('total')),
            ];

            $user->hourlyDataFlows()->createMany($data);

            if ($this->data_anomaly_notification) { // 用户流量异常警告
                $traffic = $user->hourlyDataFlows()->whereNodeId(null)->latest()->first();
                if ($traffic->total >= $this->traffic_ban_value) {
                    Notification::send(User::find(1), new DataAnomaly($user->id, flowAutoShow($traffic->u), flowAutoShow($traffic->d), flowAutoShow($traffic->traffic)));
                }
            }
        }
    }
}
