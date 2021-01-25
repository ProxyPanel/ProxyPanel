<?php

namespace App\Console\Commands;

use App\Models\Node;
use Illuminate\Console\Command;
use Log;

class NodeHourlyTrafficStatistics extends Command
{
    protected $signature = 'nodeHourlyTrafficStatistics';
    protected $description = '节点每小时流量统计';

    public function handle()
    {
        $jobStartTime = microtime(true);

        foreach (Node::whereStatus(1)->orderBy('id')->with('userDataFlowLogs')->whereHas('userDataFlowLogs')->get() as $node) {
            $this->statisticsByNode($node);
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }

    private function statisticsByNode(Node $node)
    {
        $traffic = $node->userDataFlowLogs()
            ->whereBetween('log_time', [strtotime('-1 hour'), time()])
            ->selectRaw('sum(`u`) as u, sum(`d`) as d')->first();

        if ($traffic && $total = $traffic->u + $traffic->d) { // 有数据才记录
            $node->hourlyDataFlows()->create([
                'u'       => $traffic->u,
                'd'       => $traffic->d,
                'total'   => $total,
                'traffic' => flowAutoShow($total),
            ]);
        }
    }
}
