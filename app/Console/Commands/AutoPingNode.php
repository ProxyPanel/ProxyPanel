<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\NetworkDetection;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodePing;
use Illuminate\Console\Command;
use Log;

class AutoPingNode extends Command
{
	private static $systemConfig;
	protected $signature = 'autoPingNode';
	protected $description = '节点定时Ping测速';

	public function __construct()
	{
		parent::__construct();
		self::$systemConfig = Helpers::systemConfig();
	}

	public function handle()
	{
		$jobStartTime = microtime(TRUE);

		$nodeList = SsNode::query()->where('is_transit', 0)->where('status', 1)->get();
		foreach($nodeList as $node){
			$this->pingNode($node->id, $node->is_ddns? $node->server : $node->ip);
		}

		$jobEndTime = microtime(TRUE);
		$jobUsedTime = round(($jobEndTime-$jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}

	// 节点Ping测速
	private function pingNode($nodeId, $ip)
	{
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
