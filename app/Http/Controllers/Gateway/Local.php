<?php


namespace App\Http\Controllers\Gateway;

use App\Components\Helpers;
use App\Http\Models\Goods;
use App\Http\Models\Order;
use App\Http\Models\User;
use Auth;
use Illuminate\Http\Request;
use Response;

class Local extends AbstractPayment
{
	public function purchase(Request $request)
	{
		$amount = $request->input('amount');
		$order = Order::whereOid($request->input('oid'))->first();
		$goods = Goods::query()->whereStatus(1)->whereId($request->input('goods_id'))->first();

		if($goods){
			User::query()->whereId(Auth::user()->id)->decrement('balance', $amount*100);
			// 记录余额操作日志
			Helpers::addUserBalanceLog(Auth::user()->id, $order->oid, Auth::user()->balance, Auth::user()->balance-$amount, -1*$amount, '购买商品：'.$goods->name);
		}

		self::postPayment($order->oid, '余额');

		return Response::json(['status' => 'success', 'message' => '购买完成!']);
	}

	public function notify(Request $request)
	{
		// TODO: Implement notify() method.
	}

	public function getReturnHTML(Request $request)
	{
		// TODO: Implement getReturnHTML() method.
	}

	public function getPurchaseHTML()
	{
		// TODO: Implement getPurchaseHTML() method.
	}
}