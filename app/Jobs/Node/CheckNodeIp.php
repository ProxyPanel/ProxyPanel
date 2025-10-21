<?php

namespace App\Jobs\Node;

use App\Events\NodeActions;
use App\Utils\NetworkDetection;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class CheckNodeIp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $nodeId,
        public array $ips,
        public int $port,
        public ?int $controllerNodeId = null
    ) {
    }

    public function handle(): void
    {
        foreach ($this->ips as $ip) {
            $ret = ['ip' => $ip, 'icmp' => 4, 'tcp' => 4, 'nodeId' => $this->nodeId];
            try {
                $status = NetworkDetection::networkStatus($ip, $this->port ?? 22);
                $ret['icmp'] = $status['icmp'];
                $ret['tcp'] = $status['tcp'];
            } catch (Exception $e) {
                Log::error("节点 [{$this->nodeId}] IP [$ip] 检测失败: ".$e->getMessage());
            }

            broadcast(new NodeActions('check', $ret, $this->controllerNodeId));
        }
    }
}
