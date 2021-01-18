<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplied extends Notification implements ShouldQueue
{
    use Queueable;

    private $title;
    private $content;
    private $url;

    public function __construct($title, $content, $url)
    {
        $this->title = $title;
        $this->content = $content;
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return sysConfig('ticket_replied_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('notification.reply_ticket', ['title' => $this->title]))
            ->line(trans('notification.ticket_content'))
            ->line($this->content)
            ->action(trans('notification.view_ticket'), $this->url);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
