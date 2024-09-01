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
        $message = sprintf('🛒 '.trans('common.payment.manual')."\n———————————————\n\t\tℹ️ ".trans('common.account').": %s\n\t\t💰 ".trans('user.shop.price')."：%1.2f\n\t\t📦 ".trans('model.goods.attribute').": %s\n\t\t", $order->user->username,
            $order->amount, $goods->name ?? trans('user.recharge_credit'));
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
            'title' => '🛒 '.trans('common.payment.manual'),
            'body' => [
                [
                    'keyname' => 'ℹ️ '.trans('common.account'),
                    'value' => $order->user->username,
                ],
                [
                    'keyname' => '💰 '.trans('user.shop.price'),
                    'value' => sprintf('%1.2f', $order->amount),
                ],
                [
                    'keyname' => '📦 '.trans('model.goods.attribute'),
                    'value' => $goods->name ?? trans('user.recharge_credit'),
                ],
            ],
            'markdown' => '- ℹ️ '.trans('common.account').': '.$order->user->username.PHP_EOL.'- 💰 '.trans('user.shop.price').': '.sprintf('%1.2f', $order->amount).PHP_EOL.'- 📦 '.trans('user.shop.price').': '.($goods->name ?? trans('user.recharge_credit')),
            'button' => [
                route('payment.notify', ['method' => 'manual', 'sign' => $this->sign, 'status' => 0]),
                route('payment.notify', ['method' => 'manual', 'sign' => $this->sign, 'status' => 1]),
            ],
        ];
    }
}
