<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordReset extends Notification
{
    use Queueable;

    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return sysConfig('password_reset_notification');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Reset Password Notification'))
            ->line(__('Please click on the button below to reset your password.'))
            ->action(__('Reset Password'), $this->url)
            ->line(__("If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\ninto your web browser:", ['actionText' => $this->url]))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->line(__('If you did not request a password reset, no further action is required.'));
    }
}
