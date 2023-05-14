<?php

namespace App\Notifications;

use App\Channels\BarkChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class Custom extends Notification implements ShouldQueue
{
    use Queueable;

    private $title;

    private $content;

    public function __construct($title, $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    public function via($notifiable)
    {
        return $notifiable ?? ['mail', BarkChannel::class, TelegramChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->title)
            ->markdown('mail.custom', ['content' => $this->content]);
    }

    public function toCustom($notifiable)
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content($this->content);
    }

    public function toBark($notifiable)
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'group' => '自定义信息',
            'icon' => asset('assets/images/notification/custom.png'),
            'sound' => 'newmail',
            'url_type' => 'markdown',
        ];
    }
}
