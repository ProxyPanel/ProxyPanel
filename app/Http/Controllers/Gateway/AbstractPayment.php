<?php

namespace App\Http\Controllers\Gateway;

use App\Components\Helpers;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Models\ReferralLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Str;

abstract class AbstractPayment {
	protected static $systemConfig;

	public function __construct() {
		self::$systemConfig = Helpers::systemConfig();
	}

	abstract public function purchase(Request $request): JsonResponse;

	abstract public function notify(Request $request): void;

	protected function postPayment($data, $method): int {
		// 获取需要的信息
		$payment = Payment::whereTradeNo($data)->latest()->first();
		// 是否为余额购买套餐
		if($payment){
			Payment::whereTradeNo($data)->update(['status' => 1]);
			$order = Order::find($payment->oid);
		}else{
			$order = Order::find($data);
		}
		$goods = Goods::find($order->goods_id);
		$user = User::find($order->user_id);

		//余额充值
		if($order->goods_id == 0 || $order->goods_id == null){
			Order::query()->whereOid($order->oid)->update(['status' => 2]);
			User::query()->whereId($order->user_id)->increment('credit', $order->amount * 100);
			// 余额变动记录日志
			Helpers::addUserCreditLog($order->user_id, $order->oid, $order->user->credit,
				$order->user->credit + $order->amount, $order->amount, '用户'.$method.'充值余额');

			return 0;
		}

		// 商品为流量或者套餐
		switch($goods->type){
			case 1:
				Order::query()->whereOid($order->oid)->update(['status' => 2]);
				User::query()->whereId($order->user_id)->increment('transfer_enable', $goods->traffic * MB);
				Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $user->transfer_enable,
					$user->transfer_enable + $goods->traffic * MB, '['.$method.']加上用户购买的套餐流量');
				break;
			case 2:
				$activePlan = Order::query()
				                   ->whereUserId($user->id)
				                   ->with(['goods'])
				                   ->whereIsExpire(0)
				                   ->whereStatus(2)
				                   ->whereHas('goods', static function($q) {
					                   $q->whereType(2);
				                   })
				                   ->exists();

				// 2为开始生效，3为预支付
				$order->status = $activePlan? 3 : 2;
				$order->save();

				if($activePlan){
					// 预支付订单, 刷新账号有效时间用于流量重置判断
					User::query()->whereId($order->user_id)->update([
						'expire_time' => date('Y-m-d',
							strtotime("+".$goods->days." days", strtotime($user->expire_time)))
					]);
				}else{
					// 如果买的是套餐，则先将之前购买的套餐都无效化，重置用户已用、可用流量为0
					Order::query()->whereUserId($user->id)->with(['goods'])->whereHas('goods', static function($q) {
						$q->where('type', '<=', 2);
					})->whereIsExpire(0)->whereStatus(2)->where('oid', '<>', $order->oid)->update([
						'expire_at' => date('Y-m-d H:i:s'),
						'is_expire' => 1
					]);

					User::query()->whereId($order->user_id)->update(['u' => 0, 'd' => 0, 'transfer_enable' => 0]);
					Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $user->transfer_enable, 0,
						'['.$method.']用户购买新套餐，先清空流量');

					$userTraffic = $goods->traffic * MB;
					// 添加账号有效期
					$expireTime = date('Y-m-d', strtotime("+".$goods->days." days"));
					//账号下一个重置时间
					$nextResetTime = date('Y-m-d', strtotime("+".$goods->period." days"));
					if($nextResetTime >= $expireTime){
						$nextResetTime = null;
					}

					User::query()->whereId($order->user_id)->increment('invite_num', $goods->invite_num?: 0, [
						'transfer_enable' => $userTraffic,
						'reset_time'      => $nextResetTime,
						'expire_time'     => $expireTime,
						'level'           => $goods->level,
						'enable'          => 1
					]);
					Helpers::addUserTrafficModifyLog($order->user_id, $order->oid, $user->transfer_enable, $userTraffic,
						'['.$method.']加上用户购买的套餐流量');
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
						$this->addReferralLog($order->user_id, $order->user->referral_uid, $order->oid, $order->amount,
							$order->amount * self::$systemConfig['referral_percent']);
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
	 * @param  int  $userId     用户ID
	 * @param  int  $refUserId  返利用户ID
	 * @param  int  $oid        订单ID
	 * @param  int  $amount     发生金额
	 * @param  int  $refAmount  返利金额
	 *
	 * @return int
	 */
	private function addReferralLog($userId, $refUserId, $oid, $amount, $refAmount): int {
		$log = new ReferralLog();
		$log->user_id = $userId;
		$log->ref_user_id = $refUserId;
		$log->order_id = $oid;
		$log->amount = $amount;
		$log->ref_amount = $refAmount;
		$log->status = 0;

		return $log->save();
	}

	protected function creatNewPayment($uid, $oid, $amount): Payment {
		$payment = new Payment();
		$payment->trade_no = makeRandStr(8);
		$payment->user_id = $uid;
		$payment->oid = $oid;
		$payment->amount = $amount;
		$payment->save();

		return $payment;
	}

	/**
	 * @param  string  $trade_no      本地订单号
	 * @param  string  $out_trade_no  外部订单号
	 * @param  int     $amount        交易金额
	 * @return int
	 */
	protected function addPamentCallback($trade_no, $out_trade_no, $amount): int {
		$log = new PaymentCallback();
		$log->trade_no = $trade_no;
		$log->out_trade_no = $out_trade_no;
		$log->amount = $amount;

		return $log->save();
	}
}
