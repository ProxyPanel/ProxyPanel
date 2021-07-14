<?php

namespace App\Notifications;

use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TicketReplied extends Notification implements ShouldQueue
{
    use Queueable;

    private $rely;
    private $url;
    private $is_user;

    public function __construct(TicketReply $rely, $url, $is_user = false)
    {
        $this->rely = $rely;
        $this->url = $url;
        $this->is_user = $is_user;
    }

    public function via($notifiable)
    {
        return $this->is_user ? ['mail'] : sysConfig('ticket_replied_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('notification.reply_ticket', ['title' => $this->rely->ticket->title]))
            ->line(trans('notification.ticket_content'))
            ->line(strip_tags($this->rely->content))
            ->action(trans('notification.view_ticket'), $this->url);
    }

    public function toCustom($notifiable)
    {
        return [
            'title'   => trans('notification.reply_ticket', ['title' => $this->rely->ticket->title]),
            'content' => trans('notification.ticket_content').strip_tags($this->rely->content),
        ];
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content($this->markdownMessage($this->rely))
            ->button(trans('notification.view_ticket'), $this->url);
    }

    private function markdownMessage(TicketReply $rely)
    {
        return "📮工单回复提醒 #{$rely->ticket->id}\n———————————————\n主题：\n`{$rely->ticket->title}`\n内容：\n`{$rely->content}`";
    }
}
