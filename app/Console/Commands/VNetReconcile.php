<?php

namespace App\Console\Commands;

use App\Enums\ProxyType;
use App\Jobs\VNet\ReconcileNode;
use App\Models\Node;
use Illuminate\Console\Command;
use Log;

class VNetReconcile extends Command
{
    protected $signature = 'vnet:reconcile {--node= : 仅对账指定节点}';

    protected $description = 'VNet线路用户集合对账';

    public function handle(): void
    {
        $startTime = microtime(true);

        $nodes = $this->option('node')
            ? Node::whereKey($this->option('node'))->whereType(ProxyType::VNET->value)->get()
            : Node::whereStatus(1)->whereType(ProxyType::VNET->value)->get();

        // 逐节点派发到队列：每个节点都要向节点侧发一次 HTTP 取集合，串在一起跑会拖满整个任务窗口
        foreach ($nodes as $node) {
            ReconcileNode::dispatch($node);
        }

        $jobTime = round(microtime(true) - $startTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }
}
