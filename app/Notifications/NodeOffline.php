<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NodeOffline extends Notification implements ShouldQueue
{
    use Queueable;

    private $content;

    public function __construct($nodes)
    {
        $content = '### '.trans('notification.node_offline_content')."\r\n";
        foreach ($nodes as $node) {
            $content .= "- {$node['name']} [{$node['ip']}]\r\n";
        }
        $this->content = $content;
    }

    public function via($notifiable)
    {
        return sysConfig('node_offline_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('notification.node_offline'))
            ->markdown('mail.node.offline', ['content' => $this->content]);
    }

    public function toCustom($notifiable)
    {
        return [
            'title'   => trans('notification.node_offline'),
            'content' => $this->content,
        ];
    }
}
