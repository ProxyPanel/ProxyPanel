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
        $text = sprintf('🛒 '.trans('common.payment.manual')."\n———————————————\n\t\tℹ️ ".trans('common.account').": %s\n\t\t💰 ".trans('user.shop.price')."：%1.2f\n\t\t📦 ".trans('model.goods.attribute').": %s\n\t\t", $order->user->username,
            $order->amount, $goods->name ?? trans('user.recharge_credit'));

        $message = TelegramMessage::create()
            ->token(sysConfig('telegram_token'))
            ->content($text)
            ->button(trans('common.status.reject'), route('payment.notify', ['method' => 'manual', 'sign' => $this->sign, 'status' => 0]))
            ->button(trans('common.confirm'), route('payment.notify', ['method' => 'manual', 'sign' => $this->sign, 'status' => 1]));

        // 优先发给通知对象自己绑定的会话，其次退回第一位已绑定的超管
        // （想同时通知多个管理员，请把 tgChat 渠道的 tg_chat_token 指向一个群/频道）
        $chatId = $notifiable?->telegram_user_id ?? $this->firstBoundAdminChatId();

        if (! $chatId) {
            // 不能返回 false：TelegramChannel 会直接在返回值上调用 toNotGiven() 而触发致命错误。
            // 不指定接收者时渠道会回落到 routeNotificationForTelegram() 并安全地不发消息。
            return $message;
        }

        return $message->to($chatId);
    }

    /**
     * 第一位已绑定 Telegram 的超管的 chat_id（按 id 升序，确保同一订单每次触发的收件人一致）.
     */
    protected function firstBoundAdminChatId(): ?string
    {
        // 预加载 userAuths：否则 telegram_user_id 会对每个管理员各发一条查询
        return User::role('Super Admin')->orderBy('id')->with('userAuths')->get()
            ->first(fn (User $admin) => (bool) $admin->telegram_user_id)
            ?->telegram_user_id;
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
