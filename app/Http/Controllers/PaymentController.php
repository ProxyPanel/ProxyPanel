<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Http\Controllers\Gateway\AopF2F;
use App\Http\Controllers\Gateway\BitpayX;
use App\Http\Controllers\Gateway\CodePay;
use App\Http\Controllers\Gateway\local;
use App\Http\Controllers\Gateway\PayJs;
use App\Http\Models\Coupon;
use App\Http\Models\Goods;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\PaymentCallback;
use Auth;
use Illuminate\Http\Request;
use Response;

/**
 * 支付控制器
 *
 * Class PaymentController
 *
 * @package App\Http\Controllers
 */
class PaymentController extends Controller
{
	private static $method;

	public static function getClient()
	{
		switch(self::$method){
			case 'balance':
				return new Local();
			case 'f2fpay':
				return new AopF2F();
			case 'codepay':
				return new Codepay();
			case 'payjs':
				return new PayJs();
			case 'bitpayx':
				return new BitpayX();
			default:
				return NULL;
		}
	}

	public static function notify(Request $request)
	{
		return self::getClient()->notify($request);
	}

	public static function returnHTML(Request $request)
	{
		return self::getClient()->getReturnHTML($request);
	}

	public static function purchaseHTML()
	{
		return Response::view('user.components.purchase');
	}

	public static function getStatus(Request $request)
	{
		$payment = Payment::whereSn($request->input('sn'))->first();
		if($payment->status > 0){
			return Response::json(['status' => 'success', 'data' => '', 'message' => '支付成功']);
		}elseif($payment->status < 0){
			return Response::json(['status' => 'error', 'data' => '', 'message' => '订单超时未支付，已自动关闭']);
		}else{
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '等待支付']);
		}
	}

	// 创建支付订单
	public function purchase(Request $request)
	{
		$goods_id = $request->input('goods_id');
		$coupon_sn = $request->input('coupon_sn');
		self::$method = $request->input('method');
		$balance = $request->input('amount');

		$goods = Goods::query()->whereStatus(1)->whereId($goods_id)->first();
		// 充值余额
		if($balance){
			if(!is_numeric($balance) || $balance <= 0){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '充值余额不合规']);
			}
			$amount = $balance;
			// 购买服务
		}elseif($goods_id && self::$method){
			if(!$goods){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '订单创建失败：商品或服务已下架']);
			}

			// 是否有生效的套餐
			$activePlan = Order::uid()->with(['goods'])->whereHas('goods', function($q){ $q->whereType(2); })->whereStatus(2)->whereIsExpire(0)->doesntExist();

			//无生效套餐，禁止购买加油包
			if($goods->type == 1 && $activePlan){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '购买加油包前，请先购买套餐']);
			}

			//非余额付款下，检查对应的在线支付是否开启
			if(self::$method != 'balance'){
				// 判断是否开启在线支付
				if(!Helpers::systemConfig()['is_onlinePay']){
					return Response::json(['status' => 'fail', 'data' => '', 'message' => '订单创建失败：系统并未开启在线支付功能']);
				}

				// 判断是否存在同个商品的未支付订单
				$existsOrder = Order::uid()->whereStatus(0)->whereGoodsId($goods_id)->exists();
				if($existsOrder){
					return Response::json(['status' => 'fail', 'data' => '', 'message' => '订单创建失败：尚有未支付的订单，请先去支付']);
				}
			}

			// 单个商品限购
			if($goods->limit_num){
				$count = Order::uid()->where('status', '>=', 0)->whereGoodsId($goods_id)->count();
				if($count >= $goods->limit_num){
					return Response::json(['status' => 'fail', 'data' => '', 'message' => '此商品/服务限购'.$goods->limit_num.'次，您已购买'.$count.'次']);
				}
			}

			// 使用优惠券
			if($coupon_sn){
				$coupon = Coupon::query()->whereStatus(0)->whereIn('type', [1, 2])->whereSn($coupon_sn)->first();
				if(!$coupon){
					return Response::json(['status' => 'fail', 'data' => '', 'message' => '订单创建失败：优惠券不存在']);
				}

				// 计算实际应支付总价
				$amount = $coupon->type == 2? $goods->price*$coupon->discount/10 : $goods->price-$coupon->amount;
				$amount = $amount > 0? round($amount, 2) : 0; // 四舍五入保留2位小数，避免无法正常创建订单
			}else{
				$amount = $goods->price;
			}

			// 价格异常判断
			if($amount < 0){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '订单创建失败：订单总价异常']);
			}elseif($amount == 0 && self::$method != 'balance'){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '订单创建失败：订单总价为0，无需使用在线支付']);
			}

			// 验证账号余额是否充足
			if(self::$method == 'balance' && Auth::user()->balance < $amount){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '您的余额不足，请先充值']);
			}
		}

		$orderSn = date('ymdHis').mt_rand(100000, 999999);

		// 生成订单
		$order = new Order();
		$order->order_sn = $orderSn;
		$order->user_id = Auth::user()->id;
		$order->goods_id = $balance? -1 : $goods_id;
		$order->coupon_id = !empty($coupon)? $coupon->id : 0;
		$order->origin_amount = $balance? : $goods->price;
		$order->amount = $amount;
		$order->expire_at = $balance? NULL : date("Y-m-d H:i:s", strtotime("+".$goods->days." days"));
		$order->is_expire = 0;
		$order->pay_way = self::$method;
		$order->status = 0;
		$order->save();

		// 优惠券置为已使用
		if(!empty($coupon)){
			if($coupon->usage == 1){
				$coupon->status = 1;
				$coupon->save();
			}

			Helpers::addCouponLog($coupon->id, $goods_id, $order->oid, '订单支付使用');
		}

		$request->merge(['oid' => $order->oid, 'amount' => $amount, 'type' => $request->input('pay_type')]);

		// 生成支付单
		return self::getClient()->purchase($request);
	}

	// 支付单详情
	public function detail($sn)
	{
		$payment = Payment::uid()->with(['order', 'order.goods'])->whereSn($sn)->first();
		$view['payment'] = $payment;
		$view['name'] = $payment->order->goods? $payment->order->goods->name : '余额充值';
		$view['days'] = $payment->order->goods? $payment->order->goods->days : 0;

		return Response::view('payment.detail', $view);
	}

	// 回调日志
	public function callbackList(Request $request)
	{
		$status = $request->input('status', 0);

		$query = PaymentCallback::query();

		if(isset($status)){
			$query->whereStatus($status);
		}

		$view['list'] = $query->orderBy('id', 'desc')->paginate(10)->appends($request->except('page'));

		return Response::view('payment.callbackList', $view);
	}
}