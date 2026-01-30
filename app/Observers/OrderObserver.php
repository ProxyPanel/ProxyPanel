<?php

namespace App\Observers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Notifications\PaymentConfirm;
use App\Services\OrderService;
use App\Utils\Helpers;
use Arr;
use Notification;

class OrderObserver
{
    public function updated(Order $order): void
    {
        $changes = $order->getChanges();

        // 套餐订单-流量包订单互联：订单过期时直接处理
        if (Arr::has($changes, 'is_expire') && $changes['is_expire'] === 1 && $order->goods && $order->goods->type === 2) {
            $user = $order->user;
            $oldTransferEnable = $user->transfer_enable;

            // 过期加油包
            Order::userActivePackage($order->user_id)->update(['is_expire' => 1]);

            // 检查是否有预支付订单
            $prepaidOrder = Order::userPrepay($order->user_id)->first();

            if ($prepaidOrder) {
                (new OrderService($prepaidOrder))->activatePlan();
            } else {
                // 无预支付订单：仅清理用户
                $user->update([
                    'u' => 0,
                    'd' => 0,
                    'transfer_enable' => 0,
                    'reset_time' => null,
                    'level' => 0,
                    'enable' => 0,
                ]);

                Helpers::addUserTrafficModifyLog($user->id, $oldTransferEnable, 0, trans('[Service Timer] Service Expiration'), $order->id);
            }
        }

        if (Arr::has($changes, 'status')) {
            $originalStatus = $order->getOriginal('status');

            switch ($changes['status']) {
                case -1: // 订单关闭
                    if ($order->payment) {
                        $order->payment->close();
                    }

                    if ($order->coupon) {
                        $this->returnCoupon($order, $order->coupon);
                    }

                    if ($order->goods && $order->goods->type === 2 && $originalStatus === 2 && Order::userPrepay($order->user_id)->exists()) {
                        $prepaidOrder = Order::userPrepay($order->user_id)->first();
                        (new OrderService($prepaidOrder))->activatePlan();
                    } else {
                        (new OrderService($order))->refreshAccountExpiration();
                    }
                    break;
                case 1: // 待确认支付
                    Notification::send(User::find(1), new PaymentConfirm($order));
                    break;
                case 2: // 支付成功
                    if ($originalStatus !== 3) {
                        (new OrderService($order))->receivedPayment();
                    }
                    break;
                case 3: // 预支付订单
                    if (Order::userActivePlan($order->user_id)->doesntExist()) {
                        $prepaidOrder = Order::userPrepay($order->user_id)->first();
                        if ($prepaidOrder) {
                            (new OrderService($prepaidOrder))->activatePlan();
                        }
                    } else {
                        (new OrderService($order))->refreshAccountExpiration();
                    }
                    break;
            }
        }
    }

    private function returnCoupon(Order $order, Coupon $coupon): void
    { // 退回优惠券
        if ($coupon->type !== 3 && ! $coupon->isExpired()) {
            Helpers::addCouponLog('Order canceled, coupon reinstated.', $order->coupon_id, $order->goods_id, $order->id);
            $coupon->update(['usable_times' => $coupon->usable_times + 1, 'status' => 0]);
        }
    }
}
