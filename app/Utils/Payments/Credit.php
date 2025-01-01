<?php

namespace App\Utils\Payments;

use App\Models\Goods;
use App\Models\Order;
use App\Utils\Helpers;
use App\Utils\Library\Templates\Gateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Credit implements Gateway
{
    public static array $methodDetails = [
        'key' => 'credit',
    ];

    public function purchase(Request $request): JsonResponse
    {
        $order = Order::find($request->input('id'));
        $goods = Goods::find($request->input('goods_id'));
        $user = $order->user;

        if ($user && $goods) {
            $user->update(['credit' => $user->credit - $order->amount]);
            // 记录余额操作日志
            Helpers::addUserCreditLog($user->id, $order->id, $user->credit + $order->amount, $user->credit, -1 * $order->amount, 'Purchased an item.');
        }

        $order->complete();

        return response()->json(['status' => 'success', 'message' => trans('user.purchase.completed')]);
    }

    public function notify(Request $request): void
    {
    }
}
