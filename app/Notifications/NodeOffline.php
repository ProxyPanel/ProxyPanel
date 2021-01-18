<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NodeOffline extends Notification implements ShouldQueue
{
    use Queueable;

    private $name;
    private $ip;

    public function __construct($name, $ip)
    {
        $this->name = $name;
        $this->ip = $ip;
    }

    public function via($notifiable)
    {
        return sysConfig('node_offline_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('notification.node_offline', ['name' => $this->name]))
            ->line(trans('notification.node_offline_content', ['name' => $this->name, 'ip' => $this->ip]));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
