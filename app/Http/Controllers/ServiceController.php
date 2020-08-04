<?php


namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Models\Goods;
use App\Models\Order;
use App\Models\User;

class ServiceController extends Controller {
	public function activePrepaidOrder($oid): void {
		// 取出预支付订单
		$prepaidOrder = Order::find($oid);
		//去除使用中的套餐和 流量包
		Order::query()
		     ->whereUserId($prepaidOrder->user_id)
		     ->whereStatus(2)
		     ->whereIsExpire(0)
		     ->update(['expired_at' => date('Y-m-d H:i:s'), 'is_expire' => 1]);
		//取出对应套餐信息
		$prepaidGood = Goods::find($prepaidOrder->goods_id);
		//激活预支付套餐
		Order::query()->whereOid($prepaidOrder->oid)->update([
			'expired_at' => date("Y-m-d H:i:s", strtotime("+".$prepaidGood->days." days")),
			'status'     => 2
		]);
		//取出用户信息
		$user = User::find($prepaidOrder->user_id);

		$userTraffic = $prepaidGood->traffic * MB;
		//拿出可能存在的其余套餐, 推算 最新的到期时间
		$expire_time = date('Y-m-d', strtotime("+".$prepaidGood->days." days"));
		$prepaidOrders = Order::query()->whereUserId($prepaidOrder->user_id)->whereStatus(3)->get();
		foreach($prepaidOrders as $paidOrder){
			//取出对应套餐信息
			$goods = Goods::find($paidOrder->goods_id);
			$expire_time = date('Y-m-d', strtotime("+".$goods->days." days", strtotime($expire_time)));
		}
		//计算账号下一个重置时间
		$nextResetTime = date('Y-m-d', strtotime("+".$prepaidGood->period." days"));
		if($nextResetTime >= $expire_time){
			$nextResetTime = null;
		}

		//赋予等级
		$level = $prepaidOrder->goods->level;

		Helpers::addUserTrafficModifyLog($prepaidOrder->user_id, $prepaidOrder->oid, $user->transfer_enable,
			$userTraffic, '[预支付订单激活]加上用户购买的套餐流量');
		User::query()->whereId($prepaidOrder->user_id)->increment('invite_num', $prepaidOrder->goods->invite_num?: 0, [
			'u'               => 0,
			'd'               => 0,
			'transfer_enable' => $userTraffic,
			'expire_time'     => $expire_time,
			'reset_time'      => $nextResetTime,
			'level'           => $level
		]);
	}

}
