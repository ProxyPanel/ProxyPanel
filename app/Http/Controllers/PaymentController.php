<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\Yzy;
use App\Http\Models\Coupon;
use App\Http\Models\Goods;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\PaymentCallback;
use Illuminate\Http\Request;
use Response;
use Redirect;
use Session;
use Log;
use DB;

class PaymentController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    // 创建支付单
    public function create(Request $request)
    {
        $goods_id = intval($request->get('goods_id'));
        $coupon_sn = $request->get('coupon_sn');

        $user = Session::get('user');

        $goods = Goods::query()->where('is_del', 0)->where('status', 1)->where('id', $goods_id)->first();
        if (!$goods) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：商品或服务已下架']);
        }

        // 判断是否开启有赞云支付
        if (!self::$systemConfig['is_youzan']) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：系统并未开启在线支付功能']);
        }

        // 判断是否存在同个商品的未支付订单
        $existsOrder = Order::query()->where('status', 0)->where('user_id', $user['id'])->where('goods_id', $goods_id)->exists();
        if ($existsOrder) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：尚有未支付的订单，请先去支付']);
        }

        // 限购控制
        $strategy = self::$systemConfig['goods_purchase_limit_strategy'];
        if ($strategy == 'all' || ($strategy == 'package' && $goods->type == 2) || ($strategy == 'free' && $goods->price == 0) || ($strategy == 'package&free' && ($goods->type == 2 || $goods->price == 0))) {
            $noneExpireOrderExist = Order::query()->where('status', '>=', 0)->where('is_expire', 0)->where('user_id', $user['id'])->where('goods_id', $goods_id)->exists();
            if ($noneExpireOrderExist) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：商品不可重复购买']);
            }
        }

        // 使用优惠券
        if ($coupon_sn) {
            $coupon = Coupon::query()->where('status', 0)->where('is_del', 0)->whereIn('type', [1, 2])->where('sn', $coupon_sn)->first();
            if (!$coupon) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：优惠券不存在']);
            }

            // 计算实际应支付总价
            $amount = $coupon->type == 2 ? $goods->price * $coupon->discount / 10 : $goods->price - $coupon->amount;
            $amount = $amount > 0 ? $amount : 0;
        } else {
            $amount = $goods->price;
        }

        // 价格异常判断
        if ($amount < 0) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：订单总价异常']);
        } elseif ($amount == 0) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：订单总价为0，无需使用在线支付']);
        }

        DB::beginTransaction();
        try {
            $orderSn = date('ymdHis') . mt_rand(100000, 999999);
            $sn = makeRandStr(12);

            // 生成订单
            $order = new Order();
            $order->order_sn = $orderSn;
            $order->user_id = $user['id'];
            $order->goods_id = $goods_id;
            $order->coupon_id = !empty($coupon) ? $coupon->id : 0;
            $order->origin_amount = $goods->price;
            $order->amount = $amount;
            $order->expire_at = date("Y-m-d H:i:s", strtotime("+" . $goods->days . " days"));
            $order->is_expire = 0;
            $order->pay_way = 2;
            $order->status = 0;
            $order->save();

            // 生成支付单
            $yzy = new Yzy();
            $result = $yzy->createQrCode($goods->name, $amount * 100, $orderSn);
            if (isset($result['error_response'])) {
                Log::error('【有赞云】创建二维码失败：' . $result['error_response']['msg']);

                throw new \Exception($result['error_response']['msg']);
            }

            $payment = new Payment();
            $payment->sn = $sn;
            $payment->user_id = $user['id'];
            $payment->oid = $order->oid;
            $payment->order_sn = $orderSn;
            $payment->pay_way = 1;
            $payment->amount = $amount;
            $payment->qr_id = $result['response']['qr_id'];
            $payment->qr_url = $result['response']['qr_url'];
            $payment->qr_code = $result['response']['qr_code'];
            $payment->qr_local_url = $this->base64ImageSaver($result['response']['qr_code']);
            $payment->status = 0;
            $payment->save();

            // 优惠券置为已使用
            if (!empty($coupon)) {
                if ($coupon->usage == 1) {
                    $coupon->status = 1;
                    $coupon->save();
                }

                $this->addCouponLog($coupon->id, $goods_id, $order->oid, '在线支付使用');
            }

            DB::commit();

            return Response::json(['status' => 'success', 'data' => $sn, 'message' => '创建订单成功，正在转到付款页面，请稍后']);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('创建支付订单失败：' . $e->getMessage());

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建订单失败：' . $e->getMessage()]);
        }
    }

    // 支付单详情
    public function detail(Request $request, $sn)
    {
        if (empty($sn)) {
            return Redirect::to('services');
        }

        $user = Session::get('user');

        $payment = Payment::query()->with(['order', 'order.goods'])->where('sn', $sn)->where('user_id', $user['id'])->first();
        if (!$payment) {
            return Redirect::to('services');
        }

        $order = Order::query()->where('oid', $payment->oid)->first();
        if (!$order) {
            Session::flash('errorMsg', '订单不存在');

            return Response::view('payment/' . $sn);
        }

        $view['payment'] = $payment;
        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

        return Response::view('payment.detail', $view);
    }

    // 获取订单支付状态
    public function getStatus(Request $request)
    {
        $sn = $request->get('sn');

        if (empty($sn)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '请求失败']);
        }

        $user = Session::get('user');
        $payment = Payment::query()->where('sn', $sn)->where('user_id', $user['id'])->first();
        if (!$payment) {
            return Response::json(['status' => 'error', 'data' => '', 'message' => '支付失败']);
        } elseif ($payment->status > 0) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '支付成功']);
        } elseif ($payment->status < 0) {
            return Response::json(['status' => 'error', 'data' => '', 'message' => '订单超时未支付，已自动关闭']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等待支付']);
        }
    }

    // 有赞云回调日志
    public function callbackList(Request $request)
    {
        $status = $request->get('status', 0);

        $query = PaymentCallback::query();

        if ($status) {
            $query->where('status', $status);
        }

        $view['list'] = $query->orderBy('id', 'desc')->paginate(10);

        return Response::view('payment.callbackList', $view);
    }
}