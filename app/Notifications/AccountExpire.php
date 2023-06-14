<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountExpire extends Notification implements ShouldQueue
{
    use Queueable;

    private int $days;

    public function __construct(int $expire_days)
    {
        $this->days = $expire_days;
    }

    public function via($notifiable)
    {
        return sysConfig('account_expire_notification');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('notification.account_expired'))
            ->line(trans('notification.account_expired_content', ['days' => $this->days]))
            ->action(trans('notification.view_web'), url('/'));
    }

    public function toDataBase($notifiable): array
    {
        return [
            'days' => $this->days,
        ];
    }

    public function toCustom($notifiable): array
    {
        return [
            'title' => trans('notification.account_expired'),
            'content' => trans('notification.account_expired_content', ['days' => $this->days]),
        ];
    }
}
