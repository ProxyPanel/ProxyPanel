<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

/**
 * 工单的归属判据：只有提单人可读、可回复、可关闭。
 */
class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }

    public function reply(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }
}
