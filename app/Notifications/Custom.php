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

    private string $title;

    private string $content;

    private array $channels;

    public function __construct(string $title, string $content, array $channels = ['mail', BarkChannel::class, TelegramChannel::class])
    {
        $this->title = $title;
        $this->content = $content;
        $this->channels = $channels;
    }

    public function via($notifiable): array
    {
        return $this->channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $emailAddress = config('mail.from.address');
        $atSignPosition = strpos($emailAddress, '@');

        if ($atSignPosition !== false) {
            $domain = substr($emailAddress, $atSignPosition + 1);
            $emailAddress = 'no-reply@'.$domain;
        }

        return (new MailMessage)
            ->from($emailAddress)
            ->subject($this->title)
            ->markdown('mail.custom', ['content' => $this->content]);
    }

    public function toCustom($notifiable): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content($this->content);
    }

    public function toBark($notifiable): array
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
