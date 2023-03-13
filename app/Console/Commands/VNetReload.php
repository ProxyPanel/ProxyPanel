<?php

namespace App\Console\Commands;

use App\Jobs\VNet\reloadNode;
use App\Models\Node;
use Illuminate\Console\Command;
use Log;

class VNetReload extends Command
{
    protected $signature = 'vnet:reload';
    protected $description = 'VNet线路重置';

    public function handle()
    {
        $startTime = microtime(true);

        $nodes = Node::whereStatus(1)->whereType(4)->get();
        if ($nodes->isNotEmpty()) {
            reloadNode::dispatchNow($nodes);
        }

        $jobTime = round(microtime(true) - $startTime, 4);

        Log::info('---【'.$this->description.'】完成---，耗时'.$jobTime.'秒');
    }
}
