<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketClosed extends Notification implements ShouldQueue
{
    use Queueable;

    private $title;
    private $url;
    private $reason;

    public function __construct($id, $title, $url, $reason)
    {
        $this->id = $id;
        $this->title = $title;
        $this->url = $url;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return sysConfig('ticket_closed_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('close_ticket', ['id' => $this->id, 'title' => $this->title]))
            ->line($this->reason)
            ->action(trans('notification.view_ticket'), $this->url)
            ->line(__('If your problem has not been solved, Feel free to open other one.'));
    }

    public function toArray($notifiable)
    {
        return [

        ];
    }
}
