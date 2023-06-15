<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CouponService;
use App\Utils\Helpers;
use App\Utils\Library\Templates\Gateway;
use App\Utils\Payments\CodePay;
use App\Utils\Payments\EPay;
use App\Utils\Payments\F2Fpay;
use App\Utils\Payments\Local;
use App\Utils\Payments\Manual;
use App\Utils\Payments\PayBeaver;
use App\Utils\Payments\PayJs;
use App\Utils\Payments\PayPal;
use App\Utils\Payments\Stripe;
use App\Utils\Payments\THeadPay;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class PaymentController extends Controller
{
    public static string $method;

    public static function notify(Request $request)
    {
        self::$method = $request->query('method') ?: $request->input('method');

        Log::notice(self::$method.'回调接口：'.self::$method.var_export($request->all(), true));

        return self::getClient()->notify($request);
    }

    public static function getClient(): Gateway
    {
        switch (self::$method) {
            case 'credit':
                return new Local();
            case 'f2fpay':
                return new F2Fpay();
            case 'codepay':
                return new Codepay();
            case 'payjs':
                return new PayJs();
            case 'paypal':
                return new PayPal();
            case 'epay':
                return new EPay();
            case 'stripe':
                return new Stripe();
            case 'paybeaver':
                return new PayBeaver();
            case 'theadpay':
                return new THeadPay();
            case 'manual':
                return new Manual();
            default:
                Log::emergency('未知支付：'.self::$method);
                exit(404);
        }
    }

    public static function getStatus(Request $request): JsonResponse
    {
        $payment = Payment::whereTradeNo($request->input('trade_no'))->first();
        if ($payment) {
            if ($payment->status === 1) {
                return Response::json(['status' => 'success', 'message' => '支付成功']);
            }

            if ($payment->status === -1) {
                return Response::json(['status' => 'error', 'message' => '订单超时未支付，已自动关闭']);
            }

            return Response::json(['status' => 'fail', 'message' => '等待支付']);
        }

        return Response::json(['status' => 'error', 'message' => '未知订单']);
    }

    public function purchase(Request $request) // 创建支付订单
    {
        $goods_id = $request->input('goods_id');
        $coupon_sn = $request->input('coupon_sn');
        $coupon = null;
        self::$method = $request->input('method');
        $credit = $request->input('amount');
        $pay_type = $request->input('pay_type');
        $amount = 0;

        // 充值余额
        if ($credit) {
            if (! is_numeric($credit) || $credit <= 0) {
                return Response::json(['status' => 'fail', 'message' => trans('user.payment.error')]);
            }
            $amount = $credit;
        } elseif ($goods_id && self::$method) { // 购买服务
            $goods = Goods::find($goods_id);
            if (! $goods || ! $goods->status) {
                return Response::json(['status' => 'fail', 'message' => '订单创建失败：商品已下架']);
            }
            $amount = $goods->price;

            // 是否有生效的套餐
            $activePlan = Order::userActivePlan()->doesntExist();

            //　无生效套餐，禁止购买加油包
            if ($goods->type === 1 && $activePlan) {
                return Response::json(['status' => 'fail', 'message' => '购买加油包前，请先购买套餐']);
            }

            // 单个商品限购
            if ($goods->limit_num) {
                $count = Order::uid()->where('status', '>=', 0)->whereGoodsId($goods_id)->count();
                if ($count >= $goods->limit_num) {
                    return Response::json(['status' => 'fail', 'message' => '此商品限购'.$goods->limit_num.'次，您已购买'.$count.'次']);
                }
            }

            // 使用优惠券
            if ($coupon_sn) {
                $coupon = (new CouponService($coupon_sn))->search($goods); // 检查券合规性

                if (! $coupon instanceof Coupon) {
                    return $coupon;
                }

                // 计算实际应支付总价
                $amount = $coupon->type === 2 ? $goods->price * $coupon->value / 100 : $goods->price - $coupon->value;
                $amount = $amount > 0 ? round($amount, 2) : 0; // 四舍五入保留2位小数，避免无法正常创建订单
            }

            //非余额付款下，检查在线支付是否开启
            if (self::$method !== 'credit') {
                // 判断是否开启在线支付
                if (! sysConfig('is_onlinePay') && ! sysConfig('wechat_qrcode') && ! sysConfig('alipay_qrcode')) {
                    return Response::json(['status' => 'fail', 'message' => '订单创建失败：系统并未开启在线支付功能']);
                }

                // 判断是否存在同个商品的未支付订单
                if (Order::uid()->whereStatus(0)->exists()) {
                    return Response::json(['status' => 'fail', 'message' => '订单创建失败：尚有未支付的订单，请先去支付']);
                }
            } elseif (auth()->user()->credit < $amount) { // 验证账号余额是否充足
                return Response::json(['status' => 'fail', 'message' => '您的余额不足，请先充值']);
            }

            // 价格异常判断
            if ($amount < 0) {
                return Response::json(['status' => 'fail', 'message' => '订单创建失败：订单总价异常']);
            }

            if ($amount === 0 && self::$method !== 'credit') {
                return Response::json(['status' => 'fail', 'message' => '订单创建失败：订单总价为0，无需使用在线支付']);
            }
        }

        // 生成订单
        try {
            $newOrder = Order::create([
                'sn' => date('ymdHis').random_int(100000, 999999),
                'user_id' => auth()->id(),
                'goods_id' => $credit ? null : $goods_id,
                'coupon_id' => $coupon?->id,
                'origin_amount' => $credit ?: ($goods->price ?? 0),
                'amount' => $amount,
                'pay_type' => $pay_type,
                'pay_way' => self::$method,
            ]);

            // 使用优惠券，减少可使用次数
            if ($coupon !== null) {
                if ($coupon->usable_times > 0) {
                    $coupon->decrement('usable_times');
                }

                Helpers::addCouponLog('订单支付使用', $coupon->id, $goods_id, $newOrder->id);
            }

            $request->merge(['id' => $newOrder->id, 'type' => $pay_type, 'amount' => $amount]);

            // 生成支付单
            return self::getClient()->purchase($request);
        } catch (Exception $e) {
            Log::emergency('订单生成错误：'.$e->getMessage());
        }

        return Response::json(['status' => 'fail', 'message' => '订单创建失败']);
    }

    public function close(Order $order): JsonResponse
    {
        if (! $order->close()) {
            return Response::json(['status' => 'fail', 'message' => '关闭订单失败']);
        }

        return Response::json(['status' => 'success', 'message' => '关闭订单成功']);
    }

    public function detail($trade_no) // 支付单详情
    {
        $payment = Payment::uid()->with(['order', 'order.goods'])->whereTradeNo($trade_no)->firstOrFail();
        $goods = $payment->order->goods;

        return view('user.components.payment.default', [
            'payment' => $payment,
            'name' => $goods->name ?? trans('user.recharge_credit'),
            'days' => $goods->days ?? 0,
            'pay_type' => $payment->order->pay_type_label ?: 0,
            'pay_type_icon' => $payment->order->pay_type_icon,
        ]);
    }
}
