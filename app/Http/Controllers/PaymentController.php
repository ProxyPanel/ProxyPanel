<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Http\Controllers\Gateway\BitpayX;
use App\Http\Controllers\Gateway\CodePay;
use App\Http\Controllers\Gateway\F2Fpay;
use App\Http\Controllers\Gateway\Local;
use App\Http\Controllers\Gateway\PayJs;
use App\Http\Controllers\Gateway\PayPal;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentCallback;
use Auth;
use Illuminate\Http\Request;
use Log;
use Response;

/**
 * 支付控制器
 *
 * Class PaymentController
 *
 * @package App\Http\Controllers
 */
class PaymentController extends Controller {
	private static $method;

	public static function notify(Request $request) {
		self::$method = $request->input('method');

		Log::info(self::$method."回调接口[POST]：".self::$method.var_export($request->all(), true));
		self::getClient()->notify($request);

		return 0;
	}

	public static function getClient() {
		switch(self::$method){
			case 'credit':
				return new Local();
			case 'f2fpay':
				return new F2Fpay();
			case 'codepay':
				return new Codepay();
			case 'payjs':
				return new PayJs();
			case 'bitpayx':
				return new BitpayX();
			case 'paypal':
				return new PayPal();
			default:
				Log::error("未知支付：".self::$method);

				return null;
		}
	}

	public static function returnHTML(Request $request) {
		return self::getClient()->getReturnHTML($request);
	}

	public static function purchaseHTML() {
		return Response::view('user.components.purchase');
	}

	public static function getStatus(Request $request) {
		$payment = Payment::whereTradeNo($request->input('trade_no'))->first();
		if($payment){
			if($payment->status == 1){
				return Response::json(['status' => 'success', 'message' => '支付成功']);
			}elseif($payment->status == -1){
				return Response::json(['status' => 'error', 'message' => '订单超时未支付，已自动关闭']);
			}else{
				return Response::json(['status' => 'fail', 'message' => '等待支付']);
			}
		}

		return Response::json(['status' => 'error', 'message' => '未知订单']);
	}

	// 创建支付订单
	public function purchase(Request $request) {
		$goods_id = $request->input('goods_id');
		$coupon_sn = $request->input('coupon_sn');
		self::$method = $request->input('method');
		$credit = $request->input('amount');
		$amount = 0;

		$goods = Goods::query()->whereStatus(1)->whereId($goods_id)->first();
		// 充值余额
		if($credit){
			if(!is_numeric($credit) || $credit <= 0){
				return Response::json(['status' => 'fail', 'message' => '充值余额不合规']);
			}
			$amount = $credit;
			// 购买服务
		}elseif($goods_id && self::$method){
			if(!$goods){
				return Response::json(['status' => 'fail', 'message' => '订单创建失败：商品或服务已下架']);
			}

			// 是否有生效的套餐
			$activePlan = Order::uid()->with(['goods'])->whereHas('goods', function($q) {
				$q->whereType(2);
			})->whereStatus(2)->whereIsExpire(0)->doesntExist();

			//无生效套餐，禁止购买加油包
			if($goods->type == 1 && $activePlan){
				return Response::json(['status' => 'fail', 'message' => '购买加油包前，请先购买套餐']);
			}

			//非余额付款下，检查对应的在线支付是否开启
			if(self::$method != 'credit'){
				// 判断是否开启在线支付
				if(!Helpers::systemConfig()['is_onlinePay']){
					return Response::json(['status' => 'fail', 'message' => '订单创建失败：系统并未开启在线支付功能']);
				}

				// 判断是否存在同个商品的未支付订单
				$existsOrder = Order::uid()->whereStatus(0)->whereGoodsId($goods_id)->exists();
				if($existsOrder){
					return Response::json(['status' => 'fail', 'message' => '订单创建失败：尚有未支付的订单，请先去支付']);
				}
			}

			// 单个商品限购
			if($goods->limit_num){
				$count = Order::uid()->where('status', '>=', 0)->whereGoodsId($goods_id)->count();
				if($count >= $goods->limit_num){
					return Response::json([
						'status'  => 'fail',
						'message' => '此商品/服务限购'.$goods->limit_num.'次，您已购买'.$count.'次'
					]);
				}
			}

			// 使用优惠券
			if($coupon_sn){
				$coupon = Coupon::query()->whereStatus(0)->whereIn('type', [1, 2])->whereSn($coupon_sn)->first();
				if(!$coupon){
					return Response::json(['status' => 'fail', 'message' => '订单创建失败：优惠券不存在']);
				}

				// 计算实际应支付总价
				$amount = $coupon->type == 2? $goods->price * $coupon->discount / 10 : $goods->price - $coupon->amount;
				$amount = $amount > 0? round($amount, 2) : 0; // 四舍五入保留2位小数，避免无法正常创建订单
			}else{
				$amount = $goods->price;
			}

			// 价格异常判断
			if($amount < 0){
				return Response::json(['status' => 'fail', 'message' => '订单创建失败：订单总价异常']);
			}elseif($amount == 0 && self::$method != 'credit'){
				return Response::json(['status' => 'fail', 'message' => '订单创建失败：订单总价为0，无需使用在线支付']);
			}

			// 验证账号余额是否充足
			if(self::$method == 'credit' && Auth::getUser()->credit < $amount){
				return Response::json(['status' => 'fail', 'message' => '您的余额不足，请先充值']);
			}
		}

		$orderSn = date('ymdHis').mt_rand(100000, 999999);

		// 生成订单
		$order = new Order();
		$order->order_sn = $orderSn;
		$order->user_id = Auth::id();
		$order->goods_id = $credit? -1 : $goods_id;
		$order->coupon_id = !empty($coupon)? $coupon->id : 0;
		$order->origin_amount = $credit?: $goods->price;
		$order->amount = $amount;
		$order->expire_at = $credit? null : date("Y-m-d H:i:s", strtotime("+".$goods->days." days"));
		$order->is_expire = 0;
		$order->pay_way = self::$method;
		$order->status = 0;
		$order->save();

		// 使用优惠券，减少可使用次数
		if(!empty($coupon)){
			if($coupon->usage_count > 0){
				Coupon::whereId($coupon->id)->decrement('usage_count', 1);
			}

			Helpers::addCouponLog($coupon->id, $goods_id, $order->oid, '订单支付使用');
		}

		$request->merge(['oid' => $order->oid, 'amount' => $amount, 'type' => $request->input('pay_type')]);

		// 生成支付单
		return self::getClient()->purchase($request);
	}

	// 支付单详情
	public function detail($trade_no) {
		$payment = Payment::uid()->with(['order', 'order.goods'])->whereTradeNo($trade_no)->first();
		$view['payment'] = $payment;
		$view['name'] = $payment->order->goods? $payment->order->goods->name : '余额充值';
		$view['days'] = $payment->order->goods? $payment->order->goods->days : 0;

		return Response::view('user.payment', $view);
	}

	// 回调日志
	public function callbackList(Request $request) {
		$status = $request->input('status', 0);

		$query = PaymentCallback::query();

		if(isset($status)){
			$query->whereStatus($status);
		}

		$view['list'] = $query->orderByDesc('id')->paginate(10)->appends($request->except('page'));

		return Response::view('admin.logs.callbackList', $view);
	}
}
