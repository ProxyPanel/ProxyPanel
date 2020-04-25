<?php

namespace App\Http\Controllers\Gateway;

use App\Components\Helpers;
use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\ReferralLog;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use Illuminate\Http\Request;
use Log;

abstract class AbstractPayment
{
	protected static $systemConfig;

	function __construct()
	{
		self::$systemConfig = Helpers::systemConfig();
	}

	public static function generateGuid()
	{
		mt_srand((double)microtime()*10000);
		$charid = strtoupper(md5(uniqid(mt_rand()+time(), TRUE)));
		$hyphen = chr(45);
		$uuid = chr(123)
			.substr($charid, 0, 8).$hyphen
			.substr($charid, 8, 4).$hyphen
			.substr($charid, 12, 4).$hyphen
			.substr($charid, 16, 4).$hyphen
			.substr($charid, 20, 12)
			.chr(125);
		$uuid = str_replace(['}', '{', '-'], '', $uuid);
		$uuid = substr($uuid, 0, 8);

		return $uuid;
	}

	abstract public function purchase(Request $request);


	abstract public function notify(Request $request);

	abstract public function getReturnHTML(Request $request);

	abstract public function getPurchaseHTML();

	public function postPayment($data, $method)
	{
		// 获取需要的信息
		$payment = Payment::whereSn($data)->first();
		// 是否为余额购买套餐
		if($payment){
			Payment::whereSn($data)->update(['status' => 1]);
			$order = Order::find($payment->oid);
		}else{
			$order = Order::find($data);
		}
		$goods = Goods::find($order->goods_id);
		$user = User::find($order->user_id);

		//余额充值
		if($order->goods_id == -1){
			User::query()->whereId($order->user_id)->increment('balance', $order->amount*100);
			// 余额变动记录日志
			Helpers::addUserBalanceLog($order->user_id, $order->oid, $order->user->balance, $order->user->balance+$order->amount, $order->amount, '用户'.$method.'充值余额');

			return 0;
		}

		// 商品为流量或者套餐
		switch($goods->type){
			case 1:
				$order->status = 2;
				$order->save();
				User::query()->whereId($order->user_id)->increment('transfer_enable', $goods->traffic*1048576);
				Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $user->transfer_enable, $user->transfer_enable+$goods->traffic*1048576, '['.$method.']加上用户购买的套餐流量');
				break;
			case 2:
				$activePlan = Order::query()->whereUserId($user->id)->with(['goods'])->whereIsExpire(0)->whereStatus(2)->whereHas('goods', function($q){ $q->whereType(2); })->exists();

				// 2为开始生效，3为预支付
				$order->status = $activePlan? 3 : 2;
				$order->save();

				if($activePlan){
					// 预支付订单, 刷新账号有效时间用于流量重置判断
					User::query()->whereId($order->user_id)->update(['expire_time' => date('Y-m-d', strtotime("+".$goods->days." days", strtotime($user->expire_time)))]);
				}else{
					// 如果买的是套餐，则先将之前购买的套餐都无效化，重置用户已用、可用流量为0
					Order::query()
						->whereUserId($user->id)
						->with(['goods'])
						->whereHas('goods', function($q){
							$q->where('type', '<=', 2);
						})
						->whereIsExpire(0)
						->whereStatus(2)
						->where('oid', '<>', $order->oid)
						->update(['expire_at' => date('Y-m-d H:i:s'), 'is_expire' => 1]);

					User::query()->whereId($order->user_id)->update(['u' => 0, 'd' => 0, 'transfer_enable' => 0]);
					Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $user->transfer_enable, 0, '['.$method.']用户购买新套餐，先清空流量');

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
						UserLabel::query()->whereUserId($order->user_id)->delete();

						//取出 商品默认标签  & 系统默认标签 去重
						$newUserLabels = array_values(array_unique(array_merge(GoodsLabel::query()->whereGoodsId($order->goods_id)->pluck('label_id')->toArray(), self::$systemConfig['initial_labels_for_user']? explode(',', self::$systemConfig['initial_labels_for_user']) : [])));

						// 生成标签
						foreach($newUserLabels as $Label){
							$obj = new UserLabel();
							$obj->user_id = $order->user_id;
							$obj->label_id = $Label;
							$obj->save();
						}
					}

					User::query()->whereId($order->user_id)->increment('invite_num', $goods->invite_num? : 0, ['transfer_enable' => $userTraffic, 'reset_time' => $nextResetTime, 'expire_time' => $expireTime, 'enable' => 1]);
					Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $user->transfer_enable, $userTraffic, '['.$method.']加上用户购买的套餐流量');
				}

				// 是否返利
				if(self::$systemConfig['referral_type'] && $order->user->referral_uid){
					//获取历史返利记录
					$referral = ReferralLog::whereUserId($order->user_id)->get();
					// 无记录 / 首次返利
					if(!$referral && self::$systemConfig['is_invite_register']){
						// 邀请注册功能开启时，返还邀请者邀请名额
						User::query()->whereId($order->user->referral_uid)->increment('invite_num', 1);
					}
					//按照返利模式进行返利判断
					if(self::$systemConfig['referral_type'] == 2 || (self::$systemConfig['referral_type'] == 1 && !$referral)){
						$this->addReferralLog($order->user_id, $order->user->referral_uid, $order->oid, $order->amount, $order->amount*self::$systemConfig['referral_percent']);
					}
				}

				break;
			default:
				Log::info('【处理订单】出现错误-未知套餐类型');
		}

		return 0;
	}

	/**
	 * 添加返利日志
	 *
	 * @param int $userId    用户ID
	 * @param int $refUserId 返利用户ID
	 * @param int $oid       订单ID
	 * @param int $amount    发生金额
	 * @param int $refAmount 返利金额
	 *
	 * @return int
	 */
	private function addReferralLog($userId, $refUserId, $oid, $amount, $refAmount)
	{
		$log = new ReferralLog();
		$log->user_id = $userId;
		$log->ref_user_id = $refUserId;
		$log->order_id = $oid;
		$log->amount = $amount;
		$log->ref_amount = $refAmount;
		$log->status = 0;

		return $log->save();
	}
}