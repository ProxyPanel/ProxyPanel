<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\SsNode;
use App\Http\Models\User;
use App\Http\Models\UserTrafficLog;
use App\Http\Models\UserTrafficHourly;
use Log;

class AutoStatisticsUserHourlyTraffic extends Command
{
    protected $signature = 'autoStatisticsUserHourlyTraffic';
    protected $description = '自动统计用户每小时流量';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $jobStartTime = microtime(true);

        $userList = User::query()->where('status', '>=', 0)->where('enable', 1)->get();
        foreach ($userList as $user) {
            // 统计一次所有节点的总和
            $this->statisticsByNode($user->id);

            // 统计每个节点产生的流量
            $nodeList = SsNode::query()->where('status', 1)->orderBy('id', 'asc')->get();
            foreach ($nodeList as $node) {
                $this->statisticsByNode($user->id, $node->id);
            }
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    private function statisticsByNode($user_id, $node_id = 0)
    {
        $start_time = strtotime(date('Y-m-d H:i:s', strtotime("-1 hour")));
        $end_time = time();

        $query = UserTrafficLog::query()->where('user_id', $user_id)->whereBetween('log_time', [$start_time, $end_time]);

        if ($node_id) {
            $query->where('node_id', $node_id);
        }

        $u = $query->sum('u');
        $d = $query->sum('d');
        $total = $u + $d;
        $traffic = flowAutoShow($total);

        if ($total) { // 有数据才记录
            $obj = new UserTrafficHourly();
            $obj->user_id = $user_id;
            $obj->node_id = $node_id;
            $obj->u = $u;
            $obj->d = $d;
            $obj->total = $total;
            $obj->traffic = $traffic;
            $obj->save();
        }
    }
}
