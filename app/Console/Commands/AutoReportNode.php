<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\PushNotification;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeTrafficDaily;
use Illuminate\Console\Command;
use Log;

class AutoReportNode extends Command {
	protected $signature = 'autoReportNode';
	protected $description = '自动报告节点昨日使用情况';

	public function __construct() {
		parent::__construct();
	}

	public function handle() {
		$jobStartTime = microtime(true);

		if(Helpers::systemConfig()['node_daily_report']){
			$nodeList = SsNode::query()->whereStatus(1)->get();
			if(!$nodeList->isEmpty()){
				$msg = "|节点|上行流量|下行流量|合计|\r\n| :------ | :------ | :------ |\r\n";
				foreach($nodeList as $node){
					$log = SsNodeTrafficDaily::query()
					                         ->whereNodeId($node->id)
					                         ->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime("-1 day")))
					                         ->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime("-1 day")))
					                         ->first();

					if($log){
						$msg .= '|'.$node->name.'|'.flowAutoShow($log->u).'|'.flowAutoShow($log->d).'|'.$log->traffic."\r\n";
					}else{
						$msg .= '|'.$node->name.'|'.flowAutoShow(0).'|'.flowAutoShow(0)."|0B\r\n";
					}
				}

				PushNotification::send('节点日报', $msg);
			}
		}

		$jobEndTime = microtime(true);
		$jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

		Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
	}
}
