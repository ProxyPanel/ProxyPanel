<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DataExhaust extends Notification implements ShouldQueue
{
    use Queueable;

    private $percent;

    public function __construct($percent)
    {
        $this->percent = $percent;
    }

    public function via($notifiable)
    {
        return sysConfig('data_exhaust_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('notification.traffic_warning'))
            ->line(trans('notification.traffic_remain', ['percent' => $this->percent]))
            ->line(trans('notification.traffic_tips'))
            ->action(trans('notification.view_web'), url('/'));
    }

    public function toDataBase($notifiable)
    {
        return [
            'percent' => $this->percent,
        ];
    }
}
