<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class PaymentConfirm extends Notification
{
    use Queueable;

    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    public function toTelegram($notifiable)
    {
        $order = $this->order;
        $goods = $this->order->goods;
        $sign = string_encrypt($order->payment->id);
        $message = sprintf("🛒 人工支付\n———————————————\n\t\tℹ️ 账号：%s\n\t\t💰 金额：%s\n\t\t📦 商品：%s\n\t\t", $order->user->username, $order->amount, $goods->name ?? '余额充值');
        foreach (User::role('Super Admin')->get() as $admin) {
            if (! $admin->telegram_user_id) {
                continue;
            }

            return TelegramMessage::create()
                ->to($admin->telegram_user_id)
                ->token(sysConfig('telegram_token'))
                ->content($message)
                ->button('确 认', route('payment.notify', ['method' => 'manual', 'sign' => $sign, 'status' => 1]))
                ->button('否 決', route('payment.notify', ['method' => 'manual', 'sign' => $sign, 'status' => 0]));
        }
    }
}
