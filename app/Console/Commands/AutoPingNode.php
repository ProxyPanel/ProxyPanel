<?php

namespace App\Console\Commands;

use App\Components\NetworkDetection;
use App\Models\Node;
use App\Models\NodePing;
use Illuminate\Console\Command;
use Log;

class AutoPingNode extends Command {
	protected $signature = 'autoPingNode';
	protected $description = '节点定时Ping测速';

	public function handle(): void {
		$jobStartTime = microtime(true);

		foreach(Node::whereIsRelay(0)->whereStatus(1)->get() as $node){
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
			$obj = new NodePing();
			$obj->node_id = $nodeId;
			$obj->ct = (int) $result['telecom']['time'];//电信
			$obj->cu = (int) $result['Unicom']['time'];// 联通
			$obj->cm = (int) $result['move']['time'];// 移动
			$obj->hk = (int) $result['HongKong']['time'];// 香港
			$obj->save();
		}else{
			Log::info("【".$ip."】Ping测速获取失败");
		}

	}
}
