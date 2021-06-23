<?php

namespace App\Notifications;

use Arr;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NodeBlocked extends Notification implements ShouldQueue
{
    use Queueable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return sysConfig('node_blocked_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('notification.node_block'))
            ->markdown('mail.simpleMarkdown', ['title' => trans('notification.block_report'), 'content' => $this->markdownMessage(), 'url' => route('admin.node.index')]);
    }

    private function markdownMessage()
    {
        $content = '| '.trans('user.attribute.node').' | IP | ICMP | TCP'." |\r\n| ------ | :------: | :------: |\r\n";
        $tail = '';
        foreach ($this->data as $node) {
            $case = $node;
            Arr::forget($case, ['message', 'name']);
            foreach ($case as $ip => $info) {
                $content .= "| {$node['name']} | {$ip} | ".($info['icmp'] ?? '✔️').' | '.($info['tcp'] ?? '✔️')." |\r\n";
            }
            if (Arr::hasAny($node, ['message'])) {
                $tail .= "- {$node['name']}: {$node['message']}\r\n";
            }
        }

        return $content.$tail;
    }

    public function toCustom($notifiable)
    {
        return [
            'title'   => trans('notification.node_block'),
            'content' => $this->markdownMessage(),
        ];
    }

    /**
     * @param $notifiable
     * @return TelegramMessage|\NotificationChannels\Telegram\Traits\HasSharedLogic
     */
    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content($this->markdownMessage());
    }
}
