<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NodeDailyReport extends Notification implements ShouldQueue
{
    use Queueable;

    private $content;

    public function __construct($nodes)
    {
        $content = '| '.trans('user.attribute.node').' | '.trans('notification.node.upload').' | '.trans('notification.node.download').' | '.trans('notification.node.total')." |\r\n| ------ | :------: | :------: | ------: |\r\n";
        foreach ($nodes as $node) {
            $content .= "| {$node['name']} | {$node['upload']} | {$node['download']} | {$node['total']} |\r\n";
        }
        $this->content = $content;
    }

    public function via($notifiable)
    {
        return sysConfig('node_daily_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Nodes Daily Report'))
            ->markdown('mail.node.dailyReport', ['content' => $this->content]);
    }

    public function toCustom($notifiable)
    {
        return [
            'title'   => __('Nodes Daily Report'),
            'content' => $this->content,
        ];
    }
}
