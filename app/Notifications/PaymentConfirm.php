<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Hashids\Hashids;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class PaymentConfirm extends Notification
{
    use Queueable;

    private Order $order;

    private string $sign;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->sign = (new Hashids(config('app.key'), 8))->encode($order->payment->id);
    }

    public function via($notifiable)
    {
        return sysConfig('payment_confirm_notification');
    }

    public function toTelegram($notifiable)
    {
        $order = $this->order;
        $goods = $this->order->goods;
        $message = sprintf("🛒 人工支付\n———————————————\n\t\tℹ️ 账号：%s\n\t\t💰 金额：%1.2f\n\t\t📦 商品：%s\n\t\t", $order->user->username, $order->amount, $goods->name ?? '余额充值');
        foreach (User::role('Super Admin')->get() as $admin) {
            if (! $admin->telegram_user_id) {
                continue;
            }

            return TelegramMessage::create()
                ->to($admin->telegram_user_id)
                ->token(sysConfig('telegram_token'))
                ->content($message)
                ->button(trans('common.status.reject'), route('payment.notify', ['method' => 'manual', 'sign' => $this->sign, 'status' => 0]))
                ->button(trans('common.confirm'), route('payment.notify', ['method' => 'manual', 'sign' => $this->sign, 'status' => 1]));
        }

        return false;
    }

    public function toCustom($notifiable): array
    {
        $order = $this->order;
        $goods = $this->order->goods;

        return [
            'title' => '🛒 人工支付',
            'body' => [
                [
                    'keyname' => 'ℹ️ 账号',
                    'value' => $order->user->username,
                ],
                [
                    'keyname' => '💰 金额',
                    'value' => sprintf('%1.2f', $order->amount),
                ],
                [
                    'keyname' => '📦 商品',
                    'value' => $goods->name ?? '余额充值',
                ],
            ],
            'markdown' => '- ℹ️ 账号: '.$order->user->username.PHP_EOL.'- 💰 金额: '.sprintf('%1.2f', $order->amount).PHP_EOL.'- 📦 商品: '.($goods->name ?? '余额充值'),
            'button' => [
                route('payment.notify', ['method' => 'manual', 'sign' => $this->sign, 'status' => 0]),
                route('payment.notify', ['method' => 'manual', 'sign' => $this->sign, 'status' => 1]),
            ],
        ];
    }
}
