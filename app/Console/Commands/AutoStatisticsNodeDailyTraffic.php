<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Models\NodeDailyDataFlow;
use App\Models\UserDataFlowLog;
use Illuminate\Console\Command;
use Log;

class AutoStatisticsNodeDailyTraffic extends Command
{
    protected $signature = 'autoStatisticsNodeDailyTraffic';
    protected $description = '自动统计节点每日流量';

    public function handle(): void
    {
        $jobStartTime = microtime(true);

        foreach (Node::whereStatus(1)->orderBy('id')->get() as $node) {
            $this->statisticsByNode($node->id);
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    private function statisticsByNode($node_id): void
    {
        $query = UserDataFlowLog::whereNodeId($node_id)->whereBetween('log_time', [strtotime(date('Y-m-d')), time()]);

        $u = $query->sum('u');
        $d = $query->sum('d');
        $total = $u + $d;

        if ($total) { // 有数据才记录
            $obj = new NodeDailyDataFlow();
            $obj->node_id = $node_id;
            $obj->u = $u;
            $obj->d = $d;
            $obj->total = $total;
            $obj->traffic = flowAutoShow($total);
            $obj->save();
        }
    }
}
