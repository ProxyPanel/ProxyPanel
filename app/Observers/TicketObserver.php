<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreated;
use Cache;
use Notification;

class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        Cache::forget('open_ticket_count');

        if (! $ticket->admin_id) {
            Notification::send(User::find(1), new TicketCreated($ticket, route('admin.ticket.edit', $ticket))); // 通知相关管理员
        } else {
            $ticket->user->notify(new TicketCreated($ticket, route('ticket.edit', $ticket), true));
        }
    }

    public function updated(): void
    {
        Cache::forget('open_ticket_count');
    }
}
