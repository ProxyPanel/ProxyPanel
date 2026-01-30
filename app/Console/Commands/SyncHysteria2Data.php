<?php

namespace App\Console\Commands;

use App\Jobs\Hysteria2\GetOnlineUser;
use App\Jobs\Hysteria2\GetTrafficStats;
use App\Models\Node;
use Illuminate\Console\Command;
use Log;

class SyncHysteria2Data extends Command
{
    protected $signature = 'hysteria2:sync';

    protected $description = '同步Hysteria2节点流量数据';

    public function handle(): void
    {
        $jobTime = microtime(true);
        $this->info('开始同步Hysteria2节点流量数据');

        // 同步所有Hysteria2节点
        $hysteria2Nodes = Node::where('type', 5)->where('status', 1)->get();

        if ($hysteria2Nodes->isNotEmpty()) {
            GetTrafficStats::dispatchSync($hysteria2Nodes);
            GetOnlineUser::dispatchSync($hysteria2Nodes);
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }
}
