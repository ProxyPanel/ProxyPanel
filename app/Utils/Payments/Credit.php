<?php

namespace App\Utils\Payments;

use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use App\Utils\Helpers;
use App\Utils\Library\Templates\Gateway;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Credit implements Gateway
{
    public static function metadata(): array
    {
        return [
            'key' => 'credit',
        ];
    }

    public function purchase(Request $request): JsonResponse
    {
        $order = Order::find($request->input('id'));
        $goods = Goods::find($request->input('goods_id'));
        $user = $order->user;

        if ($user && $goods) {
            // 余额判据在用户行锁里重取，扣费与日志共用同一笔事务
            $paid = DB::transaction(function () use ($user, $order): bool {
                $locked = User::whereKey($user->id)->lockForUpdate()->first();

                if ($locked->credit < $order->amount) {
                    return false;
                }

                $creditBefore = $locked->credit;
                $locked->updateCredit(-$order->amount);
                Helpers::addUserCreditLog($locked->id, $order->id, $creditBefore, $locked->credit, -1 * $order->amount, 'Purchased an item.');

                return true;
            });

            if (! $paid) {
                return response()->json(['status' => 'fail', 'message' => trans('user.payment.insufficient_balance')]);
            }
        }

        $order->complete();

        return response()->json(['status' => 'success', 'message' => trans('user.purchase.completed')]);
    }

    public function notify(Request $request): void
    {
    }
}
