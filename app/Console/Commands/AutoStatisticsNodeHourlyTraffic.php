<?php

namespace App\Console\Commands;

use App\Models\SsNode;
use App\Models\SsNodeTrafficHourly;
use App\Models\UserTrafficLog;
use Illuminate\Console\Command;
use Log;

class AutoStatisticsNodeHourlyTraffic extends Command {
	protected $signature = 'autoStatisticsNodeHourlyTraffic';
	protected $description = '自动统计节点每小时流量';

	public function handle(): void {
		$jobStartTime = microtime(true);

		foreach(SsNode::query()->whereStatus(1)->orderBy('id')->get() as $node){
			$this->statisticsByNode($node->id);
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	private function statisticsByNode($node_id): void {
		$query = UserTrafficLog::query()
		                       ->whereNodeId($node_id)
		                       ->whereBetween('log_time', [strtotime("-1 hour"), time()]);

		$u = $query->sum('u');
		$d = $query->sum('d');
		$total = $u + $d;

		if($total){ // 有数据才记录
			$obj = new SsNodeTrafficHourly();
			$obj->node_id = $node_id;
			$obj->u = $u;
			$obj->d = $d;
			$obj->total = $total;
			$obj->traffic = flowAutoShow($total);
			$obj->save();
		}
	}
}
