<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private $title;
    private $content;
    private $url;
    private $is_user;

    public function __construct($title, $content, $url, $is_user = null)
    {
        $this->title = $title;
        $this->content = $content;
        $this->url = $url;
        $this->is_user = $is_user;
    }

    public function via($notifiable)
    {
        return $this->is_user ? ['mail'] : sysConfig('ticket_created_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('notification.new_ticket', ['title' => $this->title]))
            ->line(trans('notification.ticket_content'))
            ->line($this->content)
            ->action(trans('notification.view_ticket'), $this->url);
    }

    public function toCustom($notifiable)
    {
        return [
            'title'   => trans('notification.new_ticket', ['title' => $this->title]),
            'content' => trans('notification.ticket_content').strip_tags($this->content),
        ];
    }
}
