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
        // 套餐订单-流量包订单互联
        if (Arr::has($changes, 'is_expire') && $changes['is_expire'] === 1 && $order->goods->type === 2) {
            $user = $order->user;
            $user->update([ // 清理全部流量,重置重置日期和等级
                'u' => 0,
                'd' => 0,
                'transfer_enable' => 0,
                'reset_time' => null,
                'level' => 0,
                'enable' => 0,
            ]);
            Helpers::addUserTrafficModifyLog($user->id, $user->transfer_enable, 0, __('[Service Timer] Service Expiration'), $order->id);

            Order::userActivePackage($order->user_id)->update(['is_expire' => 1]); // 过期生效中的加油包
            $this->activatePrepaidPlan($order->user_id); // 激活预支付套餐
        }

        if (Arr::has($changes, 'status')) {
            if ($changes['status'] === -1) { // 本地订单-在线订单 关闭互联
                if ($order->payment) {
                    $order->payment->close(); // 关闭在线订单
                }

                if ($order->coupon) { // 退回优惠券
                    $this->returnCoupon($order, $order->coupon);
                }

                if ($order->goods && $order->goods->type === 2 && $order->getOriginal('status') === 2 && Order::userPrepay($order->user_id)->exists()) { // 下一个套餐
                    $this->activatePrepaidPlan($order->user_id);
                } else {
                    (new OrderService($order))->refreshAccountExpiration();
                }
            } elseif ($changes['status'] === 1) { // 待确认支付 通知管理
                Notification::send(User::find(1), new PaymentConfirm($order));
            } elseif ($changes['status'] === 2 && $order->getOriginal('status') !== 3) { // 本地订单-在线订单 支付成功互联
                (new OrderService($order))->receivedPayment();
            } elseif ($changes['status'] === 3) {
                if (Order::userActivePlan($order->user_id)->doesntExist()) {
                    $this->activatePrepaidPlan($order->user_id);
                } else {
                    (new OrderService($order))->refreshAccountExpiration();
                }
            }
        }
    }

    private function activatePrepaidPlan(int $user_id): void
    { // 激活[预支付订单]
        $prepaidOrder = Order::userPrepay($user_id)->first(); // 检查该订单对应用户是否有预支付套餐
        if ($prepaidOrder) {
            (new OrderService($prepaidOrder))->activatePrepaidPlan(); // 激活预支付套餐
        }
    }

    private function returnCoupon(Order $order, Coupon $coupon): void
    { // 退回优惠券
        if ($coupon->type !== 3 && ! $coupon->isExpired()) {
            Helpers::addCouponLog('订单取消, 自动退回', $order->coupon_id, $order->goods_id, $order->id);
            $coupon->update(['usable_times' => $coupon->usable_times + 1, 'status' => 0]);
        }
    }
}
