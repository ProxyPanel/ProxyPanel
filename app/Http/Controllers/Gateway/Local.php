<?php


namespace App\Http\Controllers\Gateway;

use App\Components\Helpers;
use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Response;

class Local extends AbstractPayment {
	public function purchase($request): JsonResponse {
		$amount = $request->input('amount');
		$order = Order::whereOid($request->input('oid'))->first();
		$goods = Goods::query()->whereStatus(1)->whereId($request->input('goods_id'))->first();
		$user = Auth::getUser();

		if($user && $goods){
			User::query()->whereId($user->id)->decrement('credit', $amount * 100);
			// 记录余额操作日志
			Helpers::addUserCreditLog($user->id, $order->oid, $user->credit, $user->credit - $amount, -1 * $amount,
				'购买商品：'.$goods->name);
		}

		$this->postPayment($order->oid, '余额');

		return Response::json(['status' => 'success', 'message' => '购买完成!']);
	}

	public function notify($request): void { }
}
