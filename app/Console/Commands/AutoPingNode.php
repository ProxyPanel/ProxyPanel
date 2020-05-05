<?php

namespace App\Console\Commands;

use App\Components\NetworkDetection;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodePing;
use Illuminate\Console\Command;
use Log;

class AutoPingNode extends Command {
	protected $signature = 'autoPingNode';
	protected $description = '节点定时Ping测速';

	public function __construct() {
		parent::__construct();
	}

	public function handle() {
		$jobStartTime = microtime(true);

		$nodeList = SsNode::query()->whereIsTransit(0)->whereStatus(1)->get();
		foreach($nodeList as $node){
			$this->pingNode($node->id, $node->is_ddns? $node->server : $node->ip);
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	// 节点Ping测速
	private function pingNode($nodeId, $ip) {
		$result = NetworkDetection::ping($ip);

		if($result){
			$obj = new SsNodePing();
			$obj->node_id = $nodeId;
			$obj->ct = intval($result['China Telecom']['time']);
			$obj->cu = intval($result['China Unicom']['time']);
			$obj->cm = intval($result['China Mobile']['time']);
			$obj->hk = intval($result['Hong Kong']['time']);
			$obj->save();
		}else{
			Log::info("【".$ip."】Ping测速获取失败");
		}

	}
}
