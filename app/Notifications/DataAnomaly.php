<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DataAnomaly extends Notification implements ShouldQueue
{
    use Queueable;

    private $upload;
    private $download;
    private $total;

    public function __construct($id, $upload, $download, $total)
    {
        $this->id = $id;
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
            ->line(trans('notification.data_anomaly', ['id' => $this->id, 'upload' => $this->upload, 'download' => $this->download, 'total' => $this->total]));
    }

    public function toCustom($notifiable)
    {
        return [
            'title'   => trans('notification.data_anomaly'),
            'content' => trans('notification.data_anomaly', ['id' => $this->id, 'upload' => $this->upload, 'download' => $this->download, 'total' => $this->total]),
        ];
    }
}
