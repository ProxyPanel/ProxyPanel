<?php

namespace App\Components;

use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeLabel;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Mail\sendUserInfo;
use DB;
use Exception;
use Hash;
use Log;
use Mail;

trait Callback
{
	protected static $systemConfig;

	function __construct()
	{
		self::$systemConfig = Helpers::systemConfig();
	}

	public function show()
	{
		exit('show');
	}

	// 交易支付
	private function tradePaid($msg, $pay_type)
	{
		$pay_type_name = $pay_type == 1? '余额支付' : ($pay_type == 4? '支付宝国际' : ($pay_type == 5? '支付宝当面付' : ''));
		if($pay_type != 1){
			Log::info('【'.$pay_type_name.'】支付成功，开始处理回调订单');
			// 获取未完成状态的订单防止重复增加时间
			$payment = Payment::query()->with(['order', 'order.goods'])->where('status', 0)->where('order_sn', $msg['out_trade_no'])->first();
			if(!$payment){
				Log::info('【'.$pay_type_name.'】回调订单【'.$msg['out_trade_no'].'】不存在');

				return FALSE;
			}
		}else{
			Log::info('【'.$pay_type_name.'】订单处理');
		}

		// 处理订单
		DB::beginTransaction();
		try{
			if($pay_type != 1){
				// 如果支付单中没有用户信息则创建一个用户
				if(!$payment->user_id){
					$uid = Helpers::addUser('自动生成-'.$payment->order->email, Hash::make(makeRandStr()), 1, $payment->order->goods->days);

					if($uid){
						Order::query()->where('oid', $payment->oid)->update(['user_id' => $uid]);
					}
				}

				// 更新支付单
				$payment->pay_way = $pay_type == 4 || $pay_type == 5? 2 : 1; // 1-微信、2-支付宝
				$payment->status = 1;
				$payment->save();
			}

			// 更新订单
			$order = Order::query()->where('order_sn', $msg['out_trade_no'])->first();
			// 提取商品信息
			$goods = Goods::query()->where('id', $order->goods_id)->first();
			// 取出用户信息
			$user = User::query()->where('id', $order->user_id)->first();
			// 商品为流量或者套餐
			switch($goods->type){
				case 1:
					$order->status = 2;
					$order->save();
					Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $user->transfer_enable, $user->transfer_enable+$goods->traffic*1048576, '[在线支付]加上用户购买的套餐流量');
					User::query()->where('id', $order->user_id)->increment('transfer_enable', $goods->traffic*1048576);
					break;
				case 2:
					$activePlan = Order::query()
						->where('user_id', $order->user_id)
						->with(['goods'])
						->whereHas('goods', function($q){
							$q->where('type', 2);
						})
						->where('is_expire', 0)
						->where('status', 2)
						->exists();
					// 2为开始生效，3为预支付
					$order->status = $activePlan? 3 : 2;
					$order->save();
					// 预支付不执行
					if(!$activePlan){
						// 如果买的是套餐，则先将之前购买的套餐都无效化，重置用户已用、可用流量为0
						Order::query()
							->where('user_id', $order->user_id)
							->with(['goods'])
							->whereHas('goods', function($q){
								$q->where('type', '<=', 2);
							})
							->where('is_expire', 0)
							->where('status', 2)
							->where('oid', '<>', $order->oid)
							->update(['expire_at' => date('Y-m-d H:i:s'), 'is_expire' => 1]);

						Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $user->transfer_enable, 0, '[在线支付]用户购买新套餐，先清空流量');
						User::query()->where('id', $order->user_id)->update(['u' => 0, 'd' => 0, 'transfer_enable' => 0]);

						$userTraffic = $goods->traffic*1048576;
						// 添加账号有效期
						$expireTime = date('Y-m-d', strtotime("+".$goods->days." days"));
						//账号下一个重置时间
						$nextResetTime = date('Y-m-d', strtotime("+".$goods->period." days"));
						if($nextResetTime >= $expireTime){
							$nextResetTime = NULL;
						}

						// 写入用户标签
						if($goods->label){
							// 删除用户所有标签
							UserLabel::query()->where('user_id', $order->user_id)->delete();

							//取出 商品默认标签  & 系统默认标签 去重
							$newUserLabels = array_values(array_unique(array_merge(GoodsLabel::query()->where('goods_id', $order->goods_id)->pluck('label_id')->toArray(), self::$systemConfig['initial_labels_for_user']? explode(',', self::$systemConfig['initial_labels_for_user']) : [])));

							// 生成标签
							foreach($newUserLabels as $vo){
								$obj = new UserLabel();
								$obj->user_id = $order->user_id;
								$obj->label_id = $vo;
								$obj->save();
							}
						}

						// 写入返利日志
						if($order->user->referral_uid){
							$this->addReferralLog($order->user_id, $order->user->referral_uid, $order->oid, $order->amount, $order->amount*self::$systemConfig['referral_percent']);
							// 邀请注册功能开启时，每成功邀请一名付费用户，返还邀请者邀请名额
							if(self::$systemConfig['is_invite_register']){
								User::query()->where('id', $order->user->referral_uid)->increment('invite_num', 1);
							}
						}

						Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $user->transfer_enable, $userTraffic, '[在线支付]加上用户购买的套餐流量');
						User::query()->where('id', $order->user_id)->increment('invite_num', $goods->invite_num? : 0, ['transfer_enable' => $userTraffic, 'reset_time' => $nextResetTime, 'expire_time' => $expireTime, 'enable' => 1]);
					}else{
						//预支付订单先给上账号时间用于流量重置判断
						User::query()->where('id', $order->user_id)->update(['expire_time' => date('Y-m-d', strtotime("+".$goods->days." days", strtotime($user->expire_time)))]);
					}
					break;
				case 3:
					$order->status = 2;
					$order->save();
					User::query()->where('id', $order->user_id)->increment('balance', $goods->price*100);

					// 余额变动记录日志
					$this->addUserBalanceLog($order->user_id, $order->oid, $order->user->balance, $order->user->balance+$goods->price, $goods->price, '用户在线充值');
					break;
				default:
					Log::info('【处理订单】出现错误-未知套餐类型');
			}
			// 自动提号机：如果order的email值不为空
			if($order->email){
				$title = '自动发送账号信息';
				$content = [
					'order_sn'      => $order->order_sn,
					'goods_name'    => $order->goods->name,
					'goods_traffic' => flowAutoShow($order->goods->traffic*1048576),
					'port'          => $order->user->port,
					'passwd'        => $order->user->passwd,
					'method'        => $order->user->method,
					//'protocol'       => $order->user->protocol,
					//'protocol_param' => $order->user->protocol_param,
					//'obfs'           => $order->user->obfs,
					//'obfs_param'     => $order->user->obfs_param,
					'created_at'    => $order->created_at->toDateTimeString(),
					'expire_at'     => $order->expire_at
				];

				// 获取可用节点列表
				$labels = UserLabel::query()->where('user_id', $order->user_id)->get()->pluck('label_id');
				$nodeIds = SsNodeLabel::query()->whereIn('label_id', $labels)->get()->pluck('node_id');
				$nodeList = SsNode::query()->whereIn('id', $nodeIds)->orderBy('sort', 'desc')->orderBy('id', 'desc')->get()->toArray();
				$content['serverList'] = $nodeList;

				$logId = Helpers::addEmailLog($order->email, $title, json_encode($content));
				Mail::to($order->email)->send(new sendUserInfo($logId, $content));
			}

			DB::commit();
			Log::info('【'.$pay_type_name.'】处理成功');
		} catch(Exception $e){
			DB::rollBack();
			Log::info('【'.$pay_type_name.'】回调更新支付单和订单异常：'.$e->getMessage());
		}

		return FALSE;
	}

	private function activePrepaidOrder($oid)
	{
		// 取出预支付订单
		$prepaidOrder = Order::query()->where('oid', $oid)->first();
		//去除使用中的套餐和 流量包
		Order::query()->where('user_id', $prepaidOrder->user_id)->where('status', 2)->where('is_expire', 0)->update(['expire_at' => date('Y-m-d H:i:s'), 'is_expire' => 1]);
		//取出对应套餐信息
		$prepaidGood = Goods::query()->where('id', $prepaidOrder->goods_id)->first();
		//激活预支付套餐
		Order::query()->where('oid', $prepaidOrder->oid)->update(['expire_at' => date("Y-m-d H:i:s", strtotime("+".$prepaidGood->days." days")), 'status' => 2]);
		//取出用户信息
		$user = User::query()->where('id', $prepaidOrder->user_id)->first();

		$userTraffic = $prepaidGood->traffic*1048576;
		//拿出可能存在的其余套餐, 推算 最新的到期时间
		$expire_time = date('Y-m-d', strtotime("+".$prepaidGood->days." days"));
		$prepaidOrders = Order::query()->where('user_id', $prepaidOrder->user_id)->where('status', 3)->get();
		foreach($prepaidOrders as $paidOrder){
			//取出对应套餐信息
			$goods = Goods::query()->where('id', $paidOrder->goods_id)->first();
			$expire_time = date('Y-m-d', strtotime("+".$goods->days." days", strtotime($expire_time)));
		}
		//计算账号下一个重置时间
		$nextResetTime = date('Y-m-d', strtotime("+".$prepaidGood->period." days"));
		if($nextResetTime >= $expire_time){
			$nextResetTime = NULL;
		}

		// 用户默认标签
		$defaultLabels = self::$systemConfig['initial_labels_for_user']? explode(',', self::$systemConfig['initial_labels_for_user']) : [];
		//取出 商品默认标签  & 系统默认标签 去重
		$newUserLabels = array_values(array_unique(array_merge(GoodsLabel::query()->where('goods_id', $prepaidOrder->goods_id)->pluck('label_id')->toArray(), $defaultLabels)));

		// 生成标签
		foreach($newUserLabels as $vo){
			$obj = new UserLabel();
			$obj->user_id = $prepaidOrder->user_id;
			$obj->label_id = $vo;
			$obj->save();
		}
		Helpers::addUserTrafficModifyLog($prepaidOrder->user_id, $prepaidOrder->oid, $user->transfer_enable, $userTraffic, '[预支付订单激活]加上用户购买的套餐流量');
		User::query()->where('id', $prepaidOrder->user_id)->increment('invite_num', $prepaidOrder->invite_num? : 0, ['u' => 0, 'd' => 0, 'transfer_enable' => $userTraffic, 'expire_time' => $expire_time, 'reset_time' => $nextResetTime]);
	}
}
