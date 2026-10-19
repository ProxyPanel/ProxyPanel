<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

/**
 * 订单的归属判据：只有下单人可以关闭自己的订单。
 */
class OrderPolicy
{
    public function close(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
