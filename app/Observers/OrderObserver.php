<?php

namespace App\Observers;

use App\Components\Helpers;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Notifications\PaymentConfirm;
use App\Services\OrderService;
use Arr;
use Notification;

class OrderObserver
{
    public function updated(Order $order): void
    {
        $changes = $order->getChanges();
        if (Arr::exists($changes, 'status')) {
            if ($changes['status'] === -1) { // 本地订单-在线订单 关闭互联
                if ($order->payment) {
                    $order->payment->close(); // 关闭在线订单
                }

                if ($order->coupon && $this->returnCoupon($order->coupon)) { // 退回优惠券
                    Helpers::addCouponLog('订单超时未支付，自动退回', $order->coupon_id, $order->goods_id, $order->id);
                }
            }

            if ($changes['status'] === 1) { // 待确认支付
                Notification::send(User::find(1), new PaymentConfirm($order));
            }

            // 本地订单-在线订单 支付成功互联
            if ($changes['status'] === 2 && $order->getOriginal('status') !== 3) {
                (new OrderService($order))->receivedPayment();
            }
        }

        // 套餐订单-流量包订单互联
        if (Arr::exists($changes, 'is_expire') && $changes['is_expire'] === 1) {
            // 过期生效中的加油包
            Order::userActivePackage($order->user_id)->update(['is_expire' => 1]);

            // 检查该订单对应用户是否有预支付套餐
            $prepaidOrder = Order::userPrepay($order->user_id)->oldest()->first();

            if ($prepaidOrder) {
                (new OrderService($prepaidOrder))->activatePrepaidPlan(); // 激活预支付
            }
        }
    }

    // 返回优惠券
    private function returnCoupon(Coupon $coupon): bool
    {
        if ($coupon && $coupon->type !== 3) {
            return $coupon->update(['usable_times' => $coupon->usable_times + 1, 'status' => 0]);
        }

        return false;
    }
}
