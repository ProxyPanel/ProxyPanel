<?php

namespace App\Console\Commands;

use App\Components\PushNotification;
use App\Models\Node;
use App\Models\NodeDailyDataFlow;
use Illuminate\Console\Command;
use Log;

class AutoReportNode extends Command
{
    protected $signature = 'autoReportNode';
    protected $description = '自动报告节点昨日使用情况';

    public function handle(): void
    {
        $jobStartTime = microtime(true);

        if (sysConfig('node_daily_report')) {
            $nodeList = Node::whereStatus(1)->get();
            if ($nodeList->isNotEmpty()) {
                $msg = "|节点|上行流量|下行流量|合计|\r\n| :------ | :------ | :------ |\r\n";
                foreach ($nodeList as $node) {
                    $log = NodeDailyDataFlow::whereNodeId($node->id)
                        ->whereDate('created_at', date('Y-m-d', strtotime('-1 days')))
                        ->first();

                    if ($log) {
                        $msg .= '|'.$node->name.'|'.flowAutoShow($log->u).'|'.flowAutoShow($log->d).'|'.$log->traffic."\r\n";
                    } else {
                        $msg .= '|'.$node->name.'|'.flowAutoShow(0).'|'.flowAutoShow(0)."|0B\r\n";
                    }
                }

                PushNotification::send('节点昨日使用情况', $msg);
            }
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobUsedTime.'秒');
    }
}
