<?php

namespace App\Console\Commands;

use App\Jobs\Hysteria2\GetOnlineUser;
use App\Jobs\Hysteria2\GetTrafficStats;
use App\Models\Node;
use Illuminate\Console\Command;
use Log;
use Throwable;

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
            // 在线状态是节点列表/仪表盘的实时展示项，先采它；流量是累计值，晚一轮不影响计费。
            // 两个任务互相隔离：流量采集里任何异常（节点超时、响应结构异常）都不能连累这一分钟的在线采集。
            try {
                GetOnlineUser::dispatchSync($hysteria2Nodes);
            } catch (Throwable $exception) {
                Log::alert('【Hysteria2在线用户】同步异常：'.$exception->getMessage());
            }

            try {
                GetTrafficStats::dispatchSync($hysteria2Nodes);
            } catch (Throwable $exception) {
                Log::alert('【Hysteria2流量统计】同步异常：'.$exception->getMessage());
            }
        }

        $jobTime = round(microtime(true) - $jobTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }
}
