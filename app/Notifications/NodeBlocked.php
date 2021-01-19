<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NodeBlocked extends Notification implements ShouldQueue
{
    use Queueable;

    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function via($notifiable)
    {
        return sysConfig('node_blocked_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('notification.node_block'))
            ->line(trans('notification.block_report'))
            ->line($this->content);
    }

    public function toCustom($notifiable)
    {
        return [
            'title'   => trans('notification.node_block'),
            'content' => $this->content,
        ];
    }
}
