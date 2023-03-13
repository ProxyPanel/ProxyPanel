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
        $jobTime = microtime(true);
        User::activeUser()->with('dataFlowLogs')->WhereHas('dataFlowLogs')->chunk(config('tasks.chunk'), function ($users) {
            foreach ($users as $user) {
                $this->statisticsByUser($user);
            }
        });
        $jobTime = round(microtime(true) - $jobTime, 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobTime.'秒');
    }

    private function statisticsByUser(User $user)
    {
        $created_at = date('Y-m-d 23:59:59', strtotime('-1 days'));
        $time = strtotime($created_at);
        $logs = $user->dataFlowLogs()
            ->whereBetween('log_time', [$time - 86399, $time])
            ->groupBy('node_id')
            ->selectRaw('node_id, sum(`u`) as u, sum(`d`) as d')
            ->get();

        if ($logs->isNotEmpty()) { // 有数据才记录
            $data = $logs->each(function ($log) use ($created_at) {
                $log->total = $log->u + $log->d;
                $log->traffic = flowAutoShow($log->total);
                $log->created_at = $created_at;
            })->flatten()->toArray();

            $data[] = [ // 每日节点流量合计
                'u'          => $logs->sum('u'),
                'd'          => $logs->sum('d'),
                'total'      => $logs->sum('total'),
                'traffic'    => flowAutoShow($logs->sum('total')),
                'created_at' => $created_at,
            ];

            $user->dailyDataFlows()->createMany($data);
        }
    }
}
