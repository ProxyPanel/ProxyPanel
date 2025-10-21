<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $tradeNo,
        public string $status,
        public string $message
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('payment-status.'.$this->tradeNo);
    }

    public function broadcastAs(): string
    {
        return 'payment.status.updated';
    }
}
