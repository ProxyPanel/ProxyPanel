<?php

namespace App\Console\Commands;

use App\Models\Node;
use App\Models\User;
use App\Notifications\NodeDailyReport;
use Illuminate\Console\Command;
use Log;
use Notification;

class DailyNodeReport extends Command
{
    protected $signature = 'dailyNodeReport';
    protected $description = '自动报告节点昨日使用情况';

    public function handle()
    {
        $jobTime = microtime(true);

        if (sysConfig('node_daily_notification')) {
            $nodeList = Node::whereStatus(1)->with('dailyDataFlows')->get();
            if ($nodeList->isNotEmpty()) {
                $data = [];
                $upload = 0;
                $download = 0;
                foreach ($nodeList as $node) {
                    $log = $node->dailyDataFlows()->whereDate('created_at', date('Y-m-d', strtotime('-1 days')))->first();
                    $data[] = [
                        'name'     => $node->name,
                        'upload'   => flowAutoShow($log->u ?? 0),
                        'download' => flowAutoShow($log->d ?? 0),
                        'total'    => $log->traffic ?? '',
                    ];
                    $upload += $log->u ?? 0;
                    $download += $log->d ?? 0;
                }
                if ($data) {
                    $data[] = [
                        'name'     => trans('notification.node.total'),
                        'total'    => flowAutoShow($upload + $download),
                        'upload'   => flowAutoShow($upload),
                        'download' => flowAutoShow($download),
                    ];

                    Notification::send(User::role('Super Admin')->get(), new NodeDailyReport($data));
                }
            }
        }

        $jobTime = round((microtime(true) - $jobTime), 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobTime.'秒');
    }
}
