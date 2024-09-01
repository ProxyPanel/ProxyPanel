<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NodeRenewal extends Notification
{
    use Queueable;

    private array $nodes;

    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    public function via(object $notifiable): array
    {
        return sysConfig('node_renewal_notification');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('notification.node_renewal'))
            ->markdown('mail.simpleMarkdown', ['title' => trans('notification.node_renewal_content'), 'content' => $this->markdownMessage(), 'url' => route('admin.node.index')]);
    }

    private function markdownMessage(): string
    {
        $content = '';
        foreach ($this->nodes as $node) {
            $content .= "- $node".PHP_EOL;
        }

        return trim($content);
    }

    public function toBark(object $notifiable): array
    {
        return [
            'title' => trans('notification.node_renewal'),
            'content' => trans('notification.node_renewal_blade', ['nodes' => $this->stringMessage()]),
            'group' => trans('common.bark.node_status'),
            'icon' => asset('assets/images/notification/renewal.png'),
        ];
    }

    private function stringMessage(): string
    {
        $content = '';
        foreach ($this->nodes as $node) {
            $content .= "$node | ";
        }

        return rtrim($content, ' | '); // Remove trailing separator
    }

    public function toCustom($notifiable): array
    {
        return [
            'title' => trans('notification.node_renewal'),
            'content' => trans('notification.node_renewal_blade', ['nodes' => $this->stringMessage()]),
            'url_type' => 'markdown',
        ];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content(trans('notification.node_renewal').":\n".trans('notification.node_renewal_content')."\n".$this->markdownMessage());
    }

    public function toDataBase(object $notifiable): array
    {
        return [
            'nodes' => $this->stringMessage(),
        ];
    }
}
