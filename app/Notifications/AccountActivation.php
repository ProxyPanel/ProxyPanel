<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountActivation extends Notification
{
    use Queueable;

    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)->subject(__('Verify Email Address'))->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Your Email Address'), $this->url)
            ->line(__("If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\ninto your web browser:", ['actionText' => $this->url]))
            ->line(__('If you did not create an account, no further action is required.'));
    }
}
