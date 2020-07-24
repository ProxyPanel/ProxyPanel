<?php

namespace App\Console\Commands;

use App\Components\NetworkDetection;
use App\Models\SsNode;
use App\Models\SsNodePing;
use Illuminate\Console\Command;
use Log;

class AutoPingNode extends Command {
	protected $signature = 'autoPingNode';
	protected $description = '节点定时Ping测速';

	public function handle(): void {
		$jobStartTime = microtime(true);

		$nodeList = SsNode::query()->whereIsRelay(0)->whereStatus(1)->get();
		foreach($nodeList as $node){
			$this->pingNode($node->id, $node->is_ddns? $node->server : $node->ip);
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	// 节点Ping测速
	private function pingNode($nodeId, $ip): void {
		$result = NetworkDetection::ping($ip);

		if($result){
			$obj = new SsNodePing();
			$obj->node_id = $nodeId;
			$obj->ct = intval($result['telecom']['time']);//电信
			$obj->cu = intval($result['Unicom']['time']);// 联通
			$obj->cm = intval($result['move']['time']);// 移动
			$obj->hk = intval($result['HongKong']['time']);// 香港
			$obj->save();
		}else{
			Log::info("【".$ip."】Ping测速获取失败");
		}

	}
}
