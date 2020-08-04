<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Components\ServerChan;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeTrafficDaily;
use Illuminate\Console\Command;
use Log;

class AutoReportNode extends Command
{
    protected $signature = 'autoReportNode';
    protected $description = '自动报告节点昨日使用情况';
    protected static $systemConfig;

    public function __construct()
    {
        parent::__construct();
        self::$systemConfig = Helpers::systemConfig();
    }

    public function handle()
    {
        $jobStartTime = microtime(true);

        if (self::$systemConfig['node_daily_report']) {
            $nodeList = SsNode::query()->where('status', 1)->get();
            if (!$nodeList->isEmpty()) {
                $msg = "|节点|上行流量|下行流量|合计|\r\n| :------ | :------ | :------ |\r\n";
                foreach ($nodeList as $node) {
                    $log = SsNodeTrafficDaily::query()
                        ->where('node_id', $node->id)
                        ->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime("-1 day")))
                        ->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime("-1 day")))
                        ->first();

                    if ($log) {
                        $msg .= '|' . $node->name . '|' . flowAutoShow($log->u) . '|' . flowAutoShow($log->d) . '|' . $log->traffic . "\r\n";
                    } else {
                        $msg .= '|' . $node->name . '|' . flowAutoShow(0) . '|' . flowAutoShow(0) . "|0B\r\n";
                    }
                }

                ServerChan::send('节点日报', $msg);
            }
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }
}
