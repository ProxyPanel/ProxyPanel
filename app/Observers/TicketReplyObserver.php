<?php

namespace App\Observers;

use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TicketReplied;
use Illuminate\Database\Eloquent\Collection;
use Notification;

class TicketReplyObserver
{
    public function created(TicketReply $reply): void
    {
        $ticket = $reply->ticket;
        if ($reply->user_id) {
            if ($ticket->status !== 0) {
                $ticket->update(['status' => 0]);
            }

            Notification::send($this->findAdmin($reply), new TicketReplied($reply, route('admin.ticket.edit', $ticket))); // 通知相关管理员
        }

        if ($reply->admin_id) {
            if ($ticket->status !== 1) {
                $ticket->update(['status' => 1]);
            }

            if (sysConfig('ticket_replied_notification')) { // 通知用户
                $ticket->user->notify(new TicketReplied($reply, route('ticket.edit', $ticket), true));
            }
        }
    }

    private function findAdmin(TicketReply $reply): Collection
    {
        $ticket = $reply->ticket;
        if ($ticket->admin_id) {
            return $ticket->admin()->get();
        }

        $admins = $ticket->reply()->whereNotNull('admin_id')->distinct()->pluck('admin_id');
        if ($admins) {
            return User::findMany($admins);
        }

        return User::role('Super Admin')->get();
    }
}
