<?php

namespace App\Jobs\VNet;

use App\Models\User;
use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class editUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $data;
    private $nodes;

    public function __construct(User $user, $nodes)
    {
        $this->nodes = $nodes;
        $this->data = [
            'uid'         => $user->id,
            'port'        => (int) $user->port,
            'passwd'      => $user->passwd,
            'speed_limit' => $user->speed_limit,
            'enable'      => (int) $user->enable,
        ];
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

        $client->post('api/user/edit', $this->data);
    }
}
