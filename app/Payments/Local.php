<?php

namespace App\Payments;

use App\Components\Helpers;
use App\Models\Goods;
use App\Models\Order;
use App\Payments\Library\Gateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class Local extends Gateway
{
    public function purchase($request): JsonResponse
    {
        $order = Order::find($request->input('id'));
        $goods = Goods::find($request->input('goods_id'));
        $user = $order->user;

        if ($user && $goods) {
            $user->update(['credit' => $user->credit - $order->amount]);
            // 记录余额操作日志
            Helpers::addUserCreditLog($user->id, $order->id, $user->credit + $order->amount, $user->credit, -1 * $order->amount, '购买商品'.$goods->name);
        }

        $order->complete();

        return Response::json(['status' => 'success', 'message' => '购买完成!']);
    }

    public function notify(Request $request): void
    {
    }
}
