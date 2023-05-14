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

    private $reply;

    private $url;

    private $is_user;

    public function __construct($reply, $url, $is_user = false)
    {
        $this->reply = $reply;
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
            ->subject(trans('notification.reply_ticket', ['title' => $this->reply->ticket->title]))
            ->line(trans('notification.ticket_content'))
            ->line(strip_tags($this->reply->content))
            ->action(trans('notification.view_ticket'), $this->url);
    }

    public function toCustom($notifiable)
    {
        return [
            'title' => trans('notification.reply_ticket', ['title' => $this->reply->ticket->title]),
            'content' => trans('notification.ticket_content').strip_tags($this->reply->content),
        ];
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content($this->markdownMessage($this->reply))
            ->button(trans('notification.view_ticket'), $this->url);
    }

    private function markdownMessage(TicketReply $reply)
    {
        return "📮工单回复提醒 #{$reply->ticket->id}\n———————————————\n主题：\n`{$reply->ticket->title}`\n内容：\n`{$reply->content}`";
    }

    public function toBark($notifiable)
    {
        return [
            'title' => trans('notification.reply_ticket', ['title' => $this->reply->ticket->title]),
            'content' => trans('notification.ticket_content').strip_tags($this->reply->content),
            'group' => '工单',
            'icon' => asset('assets/images/notification/ticket.png'),
            'url' => $this->url,
        ];
    }
}
