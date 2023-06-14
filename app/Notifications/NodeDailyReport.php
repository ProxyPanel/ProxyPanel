<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NodeDailyReport extends Notification implements ShouldQueue
{
    use Queueable;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return sysConfig('node_daily_notification');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Daily Data Usage Report'))
            ->markdown('mail.simpleMarkdown', ['title' => __('Daily Data Usage Report'), 'content' => $this->markdownMessage(), 'url' => route('admin.node.index')]);
    }

    private function markdownMessage(): string
    {
        $content = '| '.trans('user.attribute.node').' | '.trans('notification.node.upload').' | '.trans('notification.node.download').' | '.trans('notification.node.total')." |\r\n| :------ | :------: | :------: | ------: |\r\n";
        foreach ($this->data as $node) {
            $content .= "| {$node['name']} | {$node['upload']} | {$node['download']} | {$node['total']} |\r\n";
        }

        return $content;
    }

    public function toCustom($notifiable): array
    {
        return [
            'title' => __('Daily Data Usage Report'),
            'content' => $this->markdownMessage(),
            'url_type' => 'markdown',
        ];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content($this->markdownMessage());
    }
}
