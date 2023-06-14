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

    public function handle(): void
    {
        $startTime = microtime(true);

        $nodes = Node::whereStatus(1)->whereType(4)->get();
        if ($nodes->isNotEmpty()) {
            reloadNode::dispatchSync($nodes);
        }

        $jobTime = round(microtime(true) - $startTime, 4);
        Log::info(__('----「:job」Completed, Used :time seconds ----', ['job' => $this->description, 'time' => $jobTime]));
    }
}
