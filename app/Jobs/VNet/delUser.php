<?php

namespace App\Jobs\VNet;

use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class delUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $userIds;
    private $nodes;

    public function __construct($userIds, $nodes)
    {
        $this->userIds = $userIds;
        $this->nodes = $nodes;
    }

    public function handle(): void
    {
        foreach ($this->nodes as $node) {
            $this->send(($node->server ?: $node->ip).':'.$node->push_port, $node->auth->secret);
        }
    }

    private function send($host, $secret): void
    {
        $client = Http::baseUrl($host)->timeout(15)->withHeaders(['secret' => $secret]);

        if (is_array($this->userIds)) {
            $client->post('api/v2/user/del/list', $this->userIds);
        } else {
            $client->post('api/user/del/'.$this->userIds);
        }
    }
}
