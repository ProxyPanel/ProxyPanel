<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Verification extends Notification
{
    use Queueable;

    private string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('notification.verification_account'))
            ->line(trans('notification.verification'))
            ->line($this->code)
            ->line(trans('notification.verification_limit', ['minutes' => config('tasks.close.verify')]));
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
