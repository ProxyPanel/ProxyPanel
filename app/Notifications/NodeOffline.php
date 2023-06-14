<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NodeOffline extends Notification implements ShouldQueue
{
    use Queueable;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return sysConfig('node_offline_notification');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('notification.node_offline'))
            ->markdown('mail.simpleMarkdown', ['title' => trans('notification.node_offline_content'), 'content' => $this->markdownMessage(), 'url' => route('admin.node.index')]);
    }

    private function markdownMessage(): string
    {
        $content = '';
        foreach ($this->data as $node) {
            $content .= "- {$node['name']} {$node['host']}\r\n";
        }

        return $content;
    }

    public function toCustom($notifiable): array
    {
        return [
            'title' => trans('notification.node_offline'),
            'content' => $this->markdownMessage(),
            'url_type' => 'markdown',
        ];
    }

    public function toBark($notifiable): array
    {
        return [
            'title' => trans('notification.node_offline'),
            'content' => $this->stringMessage(),
            'group' => '节点状态',
            'icon' => asset('assets/images/notification/offline.png'),
        ];
    }

    private function stringMessage(): string
    {
        $content = '';
        foreach ($this->data as $node) {
            $content .= "{$node['name']} {$node['host']}| ";
        }

        return $content;
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content($this->markdownMessage());
    }
}
