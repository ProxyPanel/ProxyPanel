<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserVNetTasks implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $type,
        public array $data = [],
        public ?int $userId = null
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('user.'.$this->type.'.'.($this->userId ?? 'all'));
    }

    public function broadcastAs(): string
    {
        return 'user.vnet.tasks';
    }
}
