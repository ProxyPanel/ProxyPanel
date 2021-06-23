<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class DataAnomaly extends Notification implements ShouldQueue
{
    use Queueable;

    private $userId;
    private $upload;
    private $download;
    private $total;

    public function __construct($userId, $upload, $download, $total)
    {
        $this->userId = $userId;
        $this->upload = $upload;
        $this->download = $download;
        $this->total = $total;
    }

    public function via($notifiable)
    {
        return sysConfig('data_anomaly_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('notification.data_anomaly'))
            ->line(trans('notification.data_anomaly_content', ['id' => $this->userId, 'upload' => $this->upload, 'download' => $this->download, 'total' => $this->total]));
    }

    public function toCustom($notifiable)
    {
        return [
            'title'   => trans('notification.data_anomaly'),
            'content' => trans('notification.data_anomaly_content', ['id' => $this->userId, 'upload' => $this->upload, 'download' => $this->download, 'total' => $this->total]),
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
            ->content(trans('notification.data_anomaly_content', ['id' => $this->userId, 'upload' => $this->upload, 'download' => $this->download, 'total' => $this->total]));
    }
}
