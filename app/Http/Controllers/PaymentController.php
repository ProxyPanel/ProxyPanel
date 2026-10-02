<?php

namespace App\Http\Controllers;

use App\Events\PaymentStatusUpdated;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CouponService;
use App\Utils\Helpers;
use App\Utils\Library\PaymentHelper;
use App\Utils\Library\Templates\Gateway;
use App\Utils\Payments\PaymentManager;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class PaymentController extends Controller
{
    public static string $method;

    public static function notify(Request $request)
    {
        self::$method = $request->query('method') ?: $request->input('method');

        // 先落档再分发（未知 method 与验签失败也要留记录）；留档失败不能挡住履约
        try {
            $callback = PaymentHelper::createPaymentCallback($request);

            Log::notice('[{method}] '.trans('admin.menu.log.payment_callback').' #{id}', ['method' => self::$method, 'id' => $callback->id]);
        } catch (Exception $e) {
            Log::error('['.self::$method.'] '.trans('admin.menu.log.payment_callback').': '.$e->getMessage());
        }

        return self::getClient()->notify($request);
    }

    public static function getClient(): Gateway
    {
        $method = self::$method;
        $paymentClasses = PaymentManager::discover();

        if (isset($paymentClasses[$method])) {
            try {
                return Container::getInstance()->make($paymentClasses[$method]['class']);
            } catch (Exception $e) {
                Log::emergency('Failed to instantiate payment class: '.$e->getMessage());
                abort(500);
            }
        }

        Log::emergency(trans('user.payment.order_creation.unknown_payment').': '.$method);
        abort(404);
    }

    public static function getStatus(Request $request): JsonResponse
    {
        $payment = Payment::uid()->whereTradeNo($request->input('trade_no'))->first();
        if ($payment) {
            if ($payment->status === 1) {
                // 触发支付成功事件
                broadcast(new PaymentStatusUpdated($payment->trade_no, 'success', trans('common.success_item', ['attribute' => trans('user.pay')])));

                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('user.pay')])]);
            }

            if ($payment->status === -1) {
                // 触发支付失败事件
                broadcast(new PaymentStatusUpdated($payment->trade_no, 'error', trans('user.payment.order_creation.order_timeout')));

                return response()->json(['status' => 'error', 'message' => trans('user.payment.order_creation.order_timeout')]);
            }

            return response()->json(['status' => 'fail', 'message' => trans('common.status.payment_pending')]);
        }

        return response()->json(['status' => 'error', 'message' => trans('user.payment.order_creation.unknown_order')]);
    }

    public function purchase(Request $request): JsonResponse
    { // 创建支付订单
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
                return response()->json(['status' => 'fail', 'message' => trans('user.payment.error')]);
            }
            $amount = $credit;
        } elseif ($goods_id && self::$method) { // 购买服务
            $goods = Goods::find($goods_id);
            if (! $goods || ! $goods->status) {
                return response()->json(['status' => 'fail', 'message' => trans('user.payment.order_creation.product_unavailable')]);
            }
            $amount = $goods->price;

            // 是否有生效的套餐
            $activePlan = Order::userActivePlan()->doesntExist();

            //　无生效套餐，禁止购买加油包
            if ($goods->type === 1 && $activePlan) {
                return response()->json(['status' => 'fail', 'message' => trans('user.payment.order_creation.plan_required')]);
            }

            // 单个商品限购
            if ($goods->limit_num) {
                $count = Order::uid()->where('status', '>=', 0)->whereGoodsId($goods_id)->count();
                if ($count >= $goods->limit_num) {
                    return response()->json(['status' => 'fail', 'message' => trans('user.payment.order_creation.order_limit', ['limit_num' => $goods->limit_num, 'count' => $count])]);
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

            // 非余额付款下，检查在线支付是否开启
            if (self::$method !== 'credit') {
                // 判断是否开启在线支付
                if (! sysConfig('is_onlinePay') && ! sysConfig('wechat_qrcode') && ! sysConfig('alipay_qrcode')) {
                    return response()->json(['status' => 'fail', 'message' => trans('user.payment.order_creation.payment_disabled')]);
                }

                // 判断是否存在同个商品的未支付订单
                if (Order::uid()->whereStatus(0)->exists()) {
                    return response()->json(['status' => 'fail', 'message' => trans('user.payment.order_creation.pending_order')]);
                }
            } elseif (auth()->user()->credit < $amount) { // 验证账号余额是否充足
                return response()->json(['status' => 'fail', 'message' => trans('user.payment.insufficient_balance')]);
            }

            // 价格异常判断
            if ($amount < 0) {
                return response()->json(['status' => 'fail', 'message' => trans('user.payment.order_creation.price_issue')]);
            }

            if ($amount === 0 && self::$method !== 'credit') {
                return response()->json(['status' => 'fail', 'message' => trans('user.payment.order_creation.price_zero')]);
            }
        }

        // 生成订单
        $reserved = false;

        // 重复提交只挡同一件东西：键带内容指纹，换商品或换用户都不受影响
        $createKey = 'order_create_'.auth()->id().'_'.md5(serialize([self::$method, $goods_id, $credit, $coupon_sn, $pay_type]));

        try {
            // 用认领键而不是事务行锁：purchase() 要外呼网关，锁不能跨在外部调用上
            if (! cache()->add($createKey, 1, 10)) {
                return response()->json(['status' => 'fail', 'message' => trans('auth.error.repeat_request')]);
            }

            // 券的可使用次数在建单前用条件递减占住；usable_times 为空是不限，不占也不减
            if ($coupon !== null && $coupon->usable_times > 0) {
                $reserved = (bool) Coupon::whereKey($coupon->id)->where('usable_times', '>', 0)->decrement('usable_times');

                if (! $reserved) {
                    cache()->forget($createKey);

                    return response()->json(['status' => 'fail', 'message' => trans('user.coupon.error.run_out')]);
                }
            }

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

            if ($coupon !== null) {
                Helpers::addCouponLog('Coupon used in order.', $coupon->id, $goods_id, $newOrder->id);
            }

            $request->merge(['id' => $newOrder->id, 'type' => $pay_type, 'amount' => $amount]);

            // 生成支付单
            $purchased = self::getClient()->purchase($request);

            if (($purchased->getData(true)['status'] ?? null) !== 'success') {
                cache()->forget($createKey); // 支付单没生成就不该把用户锁在门外
            }

            return $purchased;
        } catch (Exception $e) {
            cache()->forget($createKey);

            if ($reserved && ! isset($newOrder)) {
                Coupon::whereKey($coupon->id)->increment('usable_times'); // 单没建起来就把次数还回去
            }

            Log::emergency(trans('common.failed_action_item', ['action' => trans('common.create'), 'attribute' => trans('model.order.attribute')]).': '.$e->getMessage());
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_action_item', ['action' => trans('common.create'), 'attribute' => trans('model.order.attribute')])]);
    }

    public function close(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['status' => 'fail', 'message' => trans('http-statuses.401')]);
        }

        if (! $order->close()) {
            return response()->json(['status' => 'fail', 'message' => trans('common.failed_action_item', ['action' => trans('common.close'), 'attribute' => trans('model.order.attribute')])]);
        }

        return response()->json(['status' => 'success', 'message' => trans('common.success_action_item', ['action' => trans('common.close'), 'attribute' => trans('model.order.attribute')])]);
    }

    public function detail(string $trade_no): View
    { // 支付单详情
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
