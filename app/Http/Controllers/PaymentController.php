<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Http\Controllers\Gateway\BitpayX;
use App\Http\Controllers\Gateway\CodePay;
use App\Http\Controllers\Gateway\EPay;
use App\Http\Controllers\Gateway\F2Fpay;
use App\Http\Controllers\Gateway\Local;
use App\Http\Controllers\Gateway\PayJs;
use App\Http\Controllers\Gateway\PayPal;
use App\Http\Controllers\Gateway\Stripe;
use App\Http\Controllers\Gateway\PayBeaver;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Payment;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

/**
 * 支付控制器.
 *
 * Class PaymentController
 */
class PaymentController extends Controller
{
    private static $method;

    public static function notify(Request $request): int
    {
        self::$method = $request->input('method');

        Log::info(self::$method.'回调接口[POST]：'.self::$method.var_export($request->all(), true));
        self::getClient()->notify($request);

        return 0;
    }

    public static function getClient()
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
            case 'bitpayx':
                return new BitpayX();
            case 'paypal':
                return new PayPal();
            case 'epay':
                return new EPay();
            case 'stripe':
                return new Stripe();
            case 'paybeaver':
                return new PayBeaver(sysConfig('paybeaver_app_id'), sysConfig('paybeaver_app_secret'));
            default:
                Log::warning('未知支付：'.self::$method);

                return false;
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

    // 创建支付订单
    public function purchase(Request $request)
    {
        $goods_id = $request->input('goods_id');
        $coupon_sn = $request->input('coupon_sn');
        self::$method = $request->input('method');
        $credit = $request->input('amount');
        $pay_type = $request->input('pay_type');
        $amount = 0;

        // 充值余额
        if ($credit) {
            if (! is_numeric($credit) || $credit <= 0) {
                return Response::json(['status' => 'fail', 'message' => '充值余额不合规']);
            }
            $amount = $credit;
        // 购买服务
        } elseif ($goods_id && self::$method) {
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

            //非余额付款下，检查在线支付是否开启
            if (self::$method !== 'credit') {
                // 判断是否开启在线支付
                if (! sysConfig('is_onlinePay')) {
                    return Response::json(['status' => 'fail', 'message' => '订单创建失败：系统并未开启在线支付功能']);
                }

                // 判断是否存在同个商品的未支付订单
                if (Order::uid()->whereStatus(0)->exists()) {
                    return Response::json(['status' => 'fail', 'message' => '订单创建失败：尚有未支付的订单，请先去支付']);
                }
            } elseif (Auth::getUser()->credit < $amount) { // 验证账号余额是否充足
                return Response::json(['status' => 'fail', 'message' => '您的余额不足，请先充值']);
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
                $coupon = Coupon::whereStatus(0)->whereIn('type', [1, 2])->whereSn($coupon_sn)->first();
                if (! $coupon) {
                    return Response::json(['status' => 'fail', 'message' => '订单创建失败：优惠券不存在']);
                }

                // 计算实际应支付总价
                $amount = $coupon->type === 2 ? $goods->price * $coupon->value / 100 : $goods->price - $coupon->value;
                $amount = $amount > 0 ? round($amount, 2) : 0; // 四舍五入保留2位小数，避免无法正常创建订单
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
                'order_sn' => date('ymdHis').random_int(100000, 999999),
                'user_id' => auth()->id(),
                'goods_id' => $credit ? null : $goods_id,
                'coupon_id' => $coupon->id ?? null,
                'origin_amount' => $credit ?: $goods->price ?? 0,
                'amount'=>$amount,
                'pay_type'=>$pay_type,
                'pay_way'=>self::$method,
            ]);

            // 使用优惠券，减少可使用次数
            if (! empty($coupon)) {
                if ($coupon->usable_times > 0) {
                    $coupon->decrement('usable_times', 1);
                }

                Helpers::addCouponLog('订单支付使用', $coupon->id, $goods_id, $newOrder->id);
            }

            $request->merge(['id' => $newOrder->id, 'type' => $pay_type, 'amount' => $amount]);

            // 生成支付单
            return self::getClient()->purchase($request);
        } catch (Exception $e) {
            Log::error('订单生成错误：'.$e->getMessage());
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

    // 支付单详情
    public function detail($trade_no)
    {
        $payment = Payment::uid()->with(['order', 'order.goods'])->whereTradeNo($trade_no)->firstOrFail();
        $goods = $payment->order->goods;

        return view('user.payment', [
            'payment' => $payment,
            'name' => $goods->name ?? '余额充值',
            'days' => $goods->days ?? 0,
            'pay_type' => $payment->order->pay_type_label ?: 0,
            'pay_type_icon' => $payment->order->pay_type_icon,
        ]);
    }
}
