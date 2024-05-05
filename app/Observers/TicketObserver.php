<?php

namespace App\Observers;

use Cache;

class TicketObserver
{
    public function created(): void
    {
        Cache::forget('open_ticket_count');
    }

    public function updated(): void
    {
        Cache::forget('open_ticket_count');
    }
}
