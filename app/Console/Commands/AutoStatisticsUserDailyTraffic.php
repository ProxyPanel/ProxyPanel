<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Models\User;
use App\Models\UserDailyDataFlow;
use App\Models\UserDataFlowLog;
use Illuminate\Console\Command;
use Log;

class AutoStatisticsUserDailyTraffic extends Command
{
    protected $signature = 'autoStatisticsUserDailyTraffic';
    protected $description = '自动统计用户每日流量';

    public function handle(): void
    {
        $jobStartTime = microtime(true);

        foreach (User::activeUser()->get() as $user) {
            // 统计一次所有节点的总和
            $this->statisticsByUser($user->id);

            // 统计每个节点产生的流量
            foreach (Node::whereStatus(1)->orderBy('id')->get() as $node) {
                $this->statisticsByUser($user->id, $node->id);
            }
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    private function statisticsByUser($user_id, $node_id = 0): void
    {
        $query = UserDataFlowLog::whereUserId($user_id)->whereBetween('log_time', [strtotime(date('Y-m-d')), time()]);

        if ($node_id) {
            $query->whereNodeId($node_id);
        }

        $u = $query->sum('u');
        $d = $query->sum('d');
        $total = $u + $d;

        if ($total) { // 有数据才记录
            $obj = new UserDailyDataFlow();
            $obj->user_id = $user_id;
            $obj->node_id = $node_id;
            $obj->u = $u;
            $obj->d = $d;
            $obj->total = $total;
            $obj->traffic = flowAutoShow($total);
            $obj->save();
        }
    }
}
