<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private $ticket;
    private $url;
    private $is_user;

    public function __construct(Ticket $ticket, $url, $is_user = false)
    {
        $this->ticket = $ticket;
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
            ->subject(trans('notification.new_ticket', ['title' => $this->ticket->title]))
            ->line(trans('notification.ticket_content'))
            ->line($this->ticket->content)
            ->action(trans('notification.view_ticket'), $this->url);
    }

    public function toCustom($notifiable)
    {
        return [
            'title'   => trans('notification.new_ticket', ['title' => $this->ticket->title]),
            'content' => trans('notification.ticket_content').strip_tags($this->ticket->content),
        ];
    }

    /**
     * @param $notifiable
     * @return TelegramMessage|\NotificationChannels\Telegram\Traits\HasSharedLogic
     */
    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content($this->markdownMessage($this->ticket))
            ->button(trans('notification.view_ticket'), $this->url);
    }

    private function markdownMessage($ticket)
    {
        return "📮工单提醒 #{$ticket->id}\n———————————————\n主题：\n`{$ticket->title}`\n内容：\n`{$ticket->content}`";
    }
}
