<?php

namespace App\Http\Controllers;

use App\Components\AlipaySubmit;
use App\Components\Callback;
use App\Components\Helpers;
use App\Http\Models\Coupon;
use App\Http\Models\Goods;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\PaymentCallback;
use App\Http\Models\User;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;
use Log;
use Payment\Client\Charge;
use Response;
use Validator;

/**
 * 支付控制器
 *
 * Class PaymentController
 *
 * @package App\Http\Controllers
 */
class PaymentController extends Controller
{
	use Callback;

// 创建支付订单
	public function create(Request $request)
	{
		$goods_id = $request->input('goods_id');
		$coupon_sn = $request->input('coupon_sn');
		$pay_type = $request->input('pay_type');


		$goods = Goods::query()->where('status', 1)->where('id', $goods_id)->first();
		if(!$goods){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '订单创建失败：商品或服务已下架']);
		}
		// 是否有生效的套餐
		$activePlan = Order::uid()->with(['goods'])->whereHas('goods', function($q){ $q->where('type', 2); })->where('status', 2)->where('is_expire', 0)->doesntExist();
		//无生效套餐，禁止购买加油包
		if($goods->type == 1 && $activePlan){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '购买加油包前，请先购买套餐']);
		}

		//非余额付款下，检查对应的在线支付是否开启
		if($pay_type != 1){
			// 判断是否开启在线支付
			if(!self::$systemConfig['is_alipay'] && !self::$systemConfig['is_f2fpay']){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '订单创建失败：系统并未开启在线支付功能']);
			}

			// 判断是否存在同个商品的未支付订单
			$existsOrder = Order::uid()->where('status', 0)->where('goods_id', $goods_id)->exists();
			if($existsOrder){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '订单创建失败：尚有未支付的订单，请先去支付']);
			}
		}

		// 单个商品限购
		if($goods->limit_num){
			$count = Order::uid()->where('status', '>=', 0)->where('goods_id', $goods_id)->count();
			if($count >= $goods->limit_num){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '此商品/服务限购'.$goods->limit_num.'次，您已购买'.$count.'次']);
			}
		}

		// 使用优惠券
		if($coupon_sn){
			$coupon = Coupon::query()->where('status', 0)->whereIn('type', [1, 2])->where('sn', $coupon_sn)->first();
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
		}elseif($amount == 0 && $pay_type != 1){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '订单创建失败：订单总价为0，无需使用在线支付']);
		}

		// 验证账号余额是否充足
		if($pay_type == 1 && Auth::user()->balance < $amount){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '您的余额不足，请先充值']);
		}

		DB::beginTransaction();
		try{
			$orderSn = date('ymdHis').mt_rand(100000, 999999);

			// 生成订单
			$order = new Order();
			$order->order_sn = $orderSn;
			$order->user_id = Auth::user()->id;
			$order->goods_id = $goods_id;
			$order->coupon_id = !empty($coupon)? $coupon->id : 0;
			$order->origin_amount = $goods->price;
			$order->amount = $amount;
			$order->expire_at = date("Y-m-d H:i:s", strtotime("+".$goods->days." days"));
			$order->is_expire = 0;
			$order->pay_way = $pay_type;
			$order->status = 0;
			$order->save();
			// 生成支付单
			if($pay_type == 1){
				// 扣余额
				User::query()->where('id', Auth::user()->id)->decrement('balance', $amount*100);

				// 记录余额操作日志
				$this->addUserBalanceLog(Auth::user()->id, $order->oid, Auth::user()->balance, Auth::user()->balance-$amount, -1*$amount, '购买商品：'.$goods->name);

				$data = [];
				$data['out_trade_no'] = $orderSn;
				$this->tradePaid($data, 1);
			}else{
				if(self::$systemConfig['is_alipay'] && $pay_type == 4){
					$pay_way = 2;
					$parameter = [
						"service"        => "create_forex_trade", // WAP:create_forex_trade_wap ,即时到帐:create_forex_trade
						"partner"        => self::$systemConfig['alipay_partner'],
						"notify_url"     => self::$systemConfig['website_url']."/api/alipay", // 异步回调接口
						"return_url"     => self::$systemConfig['website_url'],
						"out_trade_no"   => $orderSn,  // 订单号
						"subject"        => "Package", // 订单名称
						//"total_fee"      => $amount, // 金额
						"rmb_fee"        => $amount,   // 使用RMB标价，不再使用总金额
						"body"           => "",        // 商品描述，可为空
						"currency"       => self::$systemConfig['alipay_currency'], // 结算币种
						"product_code"   => "NEW_OVERSEAS_SELLER",
						"_input_charset" => "utf-8"
					];

					// 建立请求
					$alipaySubmit = new AlipaySubmit(self::$systemConfig['alipay_sign_type'], self::$systemConfig['alipay_partner'], self::$systemConfig['alipay_key'], self::$systemConfig['alipay_private_key']);
					$result = $alipaySubmit->buildRequestForm($parameter, "post", "确认");
				}elseif(self::$systemConfig['is_f2fpay'] && $pay_type == 5){
					$pay_way = 2;
					// TODO：goods表里增加一个字段用于自定义商品付款时展示的商品名称，
					// TODO：这里增加一个随机商品列表，根据goods的价格随机取值
					$result = Charge::run("ali_qr", [
						'use_sandbox'     => FALSE,
						"partner"         => self::$systemConfig['f2fpay_app_id'],
						'app_id'          => self::$systemConfig['f2fpay_app_id'],
						'sign_type'       => 'RSA2',
						'ali_public_key'  => self::$systemConfig['f2fpay_public_key'],
						'rsa_private_key' => self::$systemConfig['f2fpay_private_key'],
						'notify_url'      => self::$systemConfig['website_url']."/api/f2fpay", // 异步回调接口
						'return_url'      => self::$systemConfig['website_url'],
						'return_raw'      => FALSE
					],
						[
							'body'     => '',
							'subject'  => self::$systemConfig['f2fpay_subject_name'],
							'order_no' => $orderSn,
							'amount'   => $amount,
						]);
				}else{
					return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：未知支付类型']);
				}
				$sn = makeRandStr(12);
				$payment = new Payment();
				$payment->sn = $sn;
				$payment->user_id = Auth::user()->id;
				$payment->oid = $order->oid;
				$payment->order_sn = $orderSn;
				$payment->pay_way = $pay_way? : 1;
				$payment->amount = $amount;
				if(self::$systemConfig['is_alipay'] && $pay_type == 4){
					$payment->qr_code = $result;
				}elseif(self::$systemConfig['is_f2fpay'] && $pay_type == 5){
					$payment->qr_code = $result;
					$payment->qr_url = 'http://qr.topscan.com/api.php?text='.$result.'&bg=ffffff&fg=000000&pt=1c73bd&m=10&w=400&el=1&inpt=1eabfc&logo=https://t.alipayobjects.com/tfscom/T1Z5XfXdxmXXXXXXXX.png';
					$payment->qr_local_url = $payment->qr_url;
				}
				$payment->status = 0;
				$payment->save();
			}

			// 优惠券置为已使用
			if(!empty($coupon)){
				if($coupon->usage == 1){
					$coupon->status = 1;
					$coupon->save();
				}

				Helpers::addCouponLog($coupon->id, $goods_id, $order->oid, '订单支付使用');
			}

			DB::commit();
			if($pay_type == 1){
				return Response::json(['status' => 'success', 'data' => '', 'message' => '支付成功']);
			}elseif($pay_type == 4){ // Alipay返回支付信息
				return Response::json(['status' => 'success', 'data' => $result, 'message' => '创建订单成功，正在转到付款页面，请稍后']);
			}elseif($pay_type == 5){
				return Response::json(['status' => 'success', 'data' => $sn, 'message' => '创建订单成功，正在转到付款页面，请稍后']);
			}
		} catch(Exception $e){
			DB::rollBack();

			Log::error('创建支付订单失败：'.$e->getMessage());

			return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建订单失败：'.$e->getMessage()]);
		}
		return Response::json(['status' => 'fail', 'data' => '', 'message' => '未知错误']);
	}

	// 支付单详情
	public function detail(Request $request, $sn)
	{
		$view['payment'] = Payment::uid()->with(['order', 'order.goods'])->where('sn', $sn)->firstOrFail();

		return Response::view('payment.detail', $view);
	}

	// 获取订单支付状态
	public function getStatus(Request $request)
	{
		$validator = Validator::make($request->all(), ['sn' => 'required|exists:payment,sn'], ['sn.required' => '请求失败：缺少sn', 'sn.exists' => '支付失败：支付单不存在']);

		if($validator->fails()){
			return Response::json(['status' => 'error', 'data' => '', 'message' => $validator->getMessageBag()->first()]);
		}

		$payment = Payment::uid()->where('sn', $request->sn)->first();
		if($payment->status > 0){
			return Response::json(['status' => 'success', 'data' => '', 'message' => '支付成功']);
		}elseif($payment->status < 0){
			return Response::json(['status' => 'error', 'data' => '', 'message' => '订单超时未支付，已自动关闭']);
		}else{
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '等待支付']);
		}
	}

	// 回调日志
	public function callbackList(Request $request)
	{
		$status = $request->input('status', 0);

		$query = PaymentCallback::query();

		if(isset($status)){
			$query->where('status', $status);
		}

		$view['list'] = $query->orderBy('id', 'desc')->paginate(10)->appends($request->except('page'));

		return Response::view('payment.callbackList', $view);
	}
}