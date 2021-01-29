<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    private $amount;
    private $sn;

    public function __construct($sn, $amount)
    {
        $this->amount = $amount;
        $this->sn = $sn;
    }

    public function via($notifiable)
    {
        return sysConfig('payment_received_notification');
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Payment Received'))
            ->line(__('Payment for #:sn has been received! Total amount: ¥:amount.', ['sn' => $this->sn, 'amount' => $this->amount]))
            ->action(__('Invoice Detail'), route('invoiceInfo', $this->sn));
    }

    public function toDataBase($notifiable)
    {
        return [
            'sn'     => $this->sn,
            'amount' => $this->amount,
        ];
    }
}
