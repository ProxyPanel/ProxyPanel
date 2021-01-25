<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Log;

class UserDailyTrafficStatistics extends Command
{
    protected $signature = 'userDailyTrafficStatistics';
    protected $description = '用户每日流量统计';

    public function handle()
    {
        $jobStartTime = microtime(true);
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
            ->whereBetween('log_time', [strtotime(date('Y-m-d')), time()])
            ->groupBy('node_id')
            ->selectRaw('node_id, sum(`u`) as u, sum(`d`) as d')
            ->get();

        if ($logs->isNotEmpty()) { // 有数据才记录
            $data = $logs->each(function ($log) {
                $log->total = $log->u + $log->d;
                $log->traffic = flowAutoShow($log->total);
            })->flatten()->toArray();

            $data[] = [ // 每日节点流量合计
                'u'       => $logs->sum('u'),
                'd'       => $logs->sum('d'),
                'total'   => $logs->sum('total'),
                'traffic' => flowAutoShow($logs->sum('total')),
            ];

            $user->dailyDataFlows()->createMany($data);
        }
    }
}
