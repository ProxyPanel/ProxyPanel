<?php


namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Order;
use App\Http\Models\User;
use App\Http\Models\UserLabel;

class ServiceController extends Controller
{
	public function activePrepaidOrder($oid)
	{
		// 取出预支付订单
		$prepaidOrder = Order::find($oid);
		//去除使用中的套餐和 流量包
		Order::query()->whereUserId($prepaidOrder->user_id)->whereStatus(2)->whereIsExpire(0)->update(['expire_at' => date('Y-m-d H:i:s'), 'is_expire' => 1]);
		//取出对应套餐信息
		$prepaidGood = Goods::query()->whereId($prepaidOrder->goods_id)->first();
		//激活预支付套餐
		Order::query()->whereOid($prepaidOrder->oid)->update(['expire_at' => date("Y-m-d H:i:s", strtotime("+".$prepaidGood->days." days")), 'status' => 2]);
		//取出用户信息
		$user = User::query()->whereId($prepaidOrder->user_id)->first();

		$userTraffic = $prepaidGood->traffic*1048576;
		//拿出可能存在的其余套餐, 推算 最新的到期时间
		$expire_time = date('Y-m-d', strtotime("+".$prepaidGood->days." days"));
		$prepaidOrders = Order::query()->whereUserId($prepaidOrder->user_id)->whereStatus(3)->get();
		foreach($prepaidOrders as $paidOrder){
			//取出对应套餐信息
			$goods = Goods::query()->whereId($paidOrder->goods_id)->first();
			$expire_time = date('Y-m-d', strtotime("+".$goods->days." days", strtotime($expire_time)));
		}
		//计算账号下一个重置时间
		$nextResetTime = date('Y-m-d', strtotime("+".$prepaidGood->period." days"));
		if($nextResetTime >= $expire_time){
			$nextResetTime = NULL;
		}

		// 用户默认标签
		$defaultLabels = Helpers::systemConfig()['initial_labels_for_user']? explode(',', Helpers::systemConfig()['initial_labels_for_user']) : [];
		//取出 商品默认标签  & 系统默认标签 去重
		$newUserLabels = array_values(array_unique(array_merge(GoodsLabel::query()->whereGoodsId($prepaidOrder->goods_id)->pluck('label_id')->toArray(), $defaultLabels)));

		// 生成标签
		foreach($newUserLabels as $vo){
			$obj = new UserLabel();
			$obj->user_id = $prepaidOrder->user_id;
			$obj->label_id = $vo;
			$obj->save();
		}
		Helpers::addUserTrafficModifyLog($prepaidOrder->user_id, $prepaidOrder->oid, $user->transfer_enable, $userTraffic, '[预支付订单激活]加上用户购买的套餐流量');
		User::query()->whereId($prepaidOrder->user_id)->increment('invite_num', $prepaidOrder->invite_num? : 0, ['u' => 0, 'd' => 0, 'transfer_enable' => $userTraffic, 'expire_time' => $expire_time, 'reset_time' => $nextResetTime]);
	}

}