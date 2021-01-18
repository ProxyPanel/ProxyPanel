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
        $temp = '| '.trans('user.attribute.node').' | '.trans('notification.node.upload').' | '.trans('notification.node.download').' | '.trans('notification.node.total')." |\r\n| ------ | :------: | :------: | ------: |\r\n";
        foreach ($nodes as $node) {
            $temp .= "| {$node['name']} | {$node['upload']} | {$node['download']} | {$node['total']} |\r\n";
        }
        $this->content = $temp;
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
