<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NodeActions implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $type,
        public array $data = [],
        public ?int $nodeId = null
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('node.'.$this->type.'.'.($this->nodeId ?? 'all'));
    }

    public function broadcastAs(): string
    {
        return 'node.actions';
    }
}
