<?php

namespace App\Http\Controllers\Api;

use App\Components\Yzy;
use App\Http\Controllers\Controller;
use App\Http\Models\Coupon;
use App\Http\Models\CouponLog;
use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\PaymentCallback;
use App\Http\Models\ReferralLog;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use Illuminate\Http\Request;
use Log;
use DB;

/**
 * 有赞云支付
 * Class YzyController
 *
 * @package App\Http\Controllers
 */
class YzyController extends Controller
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    // 接收GET请求
    public function index(Request $request)
    {
        \Log::info("YZY-GET:" . var_export($request->all()));
    }

    // 接收POST请求
    public function store(Request $request)
    {
        \Log::info("YZY-POST:" . var_export($request->all()));

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if (!$data) {
            Log::info('YZY-POST:回调数据无法解析，可能是非法请求');
            exit();
        }

        // 判断消息是否合法
        $msg = $data['msg'];
        $sign_string = self::$config['youzan_client_id'] . "" . $msg . "" . self::$config['youzan_client_secret'];
        $sign = md5($sign_string);
        if ($sign != $data['sign']) {
            Log::info('YZY-POST:回调数据签名错误，可能是非法请求');
            exit();
        } else {
            // 返回请求成功标识给有赞
            var_dump(["code" => 0, "msg" => "success"]);
        }

        // 先写入回调日志
        $this->callbackLog($data['client_id'], $data['id'], $data['kdt_id'], $data['kdt_name'], $data['mode'], $data['msg'], $data['sendCount'], $data['sign'], $data['status'], $data['test'], $data['type'], $data['version']);

        // msg内容经过 urlencode 编码，进行解码
        $msg = json_decode(urldecode($msg), true);

        if ($data['type'] == 'TRADE_ORDER_STATE') {
            // 读取订单信息
            $yzy = new Yzy();
            $result = $yzy->getTradeByTid($msg['tid']);
            if (isset($result['error_response'])) {
                Log::info('【有赞云】回调订单信息错误：' . $result['error_response']['msg']);
                exit();
            }

            $payment = Payment::query()->where('qr_id', $result['response']['trade']['qr_id'])->first();
            if (!$payment) {
                Log::info('【有赞云】回调订单不存在');
                exit();
            }

            // 等待支付
            if ($data['status'] == 'WAIT_BUYER_PAY') {
                Log::info('【有赞云】等待支付' . urldecode($data['msg']));
                exit();
            }

            // 交易成功
            if ($data['status'] == 'TRADE_SUCCESS') {
                if ($payment->status != '0') {
                    Log::info('【有赞云】回调订单状态不正确');
                    exit();
                }

                // 处理订单
                DB::beginTransaction();
                try {
                    // 更新支付单
                    $payment->pay_way = $msg['pay_type'] == '微信支付' ? 1 : 2; // 1-微信、2-支付宝
                    $payment->status = 1;
                    $payment->save();

                    // 更新订单
                    $order = Order::query()->with(['user'])->where('oid', $payment->oid)->first();
                    $order->status = 2;
                    $order->save();

                    // 优惠券置为已使用
                    $coupon = Coupon::query()->where('id', $order->coupon_id)->first();
                    if ($coupon) {
                        if ($coupon->usage == 1) {
                            $coupon->status = 1;
                            $coupon->save();
                        }

                        // 写入日志
                        $couponLog = new CouponLog();
                        $couponLog->coupon_id = $coupon->id;
                        $couponLog->goods_id = $order->goods_id;
                        $couponLog->order_id = $order->oid;
                        $couponLog->save();
                    }

                    // 如果买的是套餐，则先将之前购买的所有套餐置都无效，并扣掉之前所有套餐的流量
                    $goods = Goods::query()->where('id', $order->goods_id)->first();
                    if ($goods->type == 2) {
                        $existOrderList = Order::query()
                            ->with(['goods'])
                            ->whereHas('goods', function ($q) {
                                $q->where('type', 2);
                            })
                            ->where('user_id', $order->user_id)
                            ->where('oid', '<>', $order->oid)
                            ->where('is_expire', 0)
                            ->get();

                        foreach ($existOrderList as $vo) {
                            Order::query()->where('oid', $vo->oid)->update(['is_expire' => 1]);
                            User::query()->where('id', $order->user_id)->decrement('transfer_enable', $vo->goods->traffic * 1048576);
                        }
                    }

                    // 把商品的流量加到账号上
                    User::query()->where('id', $order->user_id)->increment('transfer_enable', $goods->traffic * 1048576);

                    // 套餐就改流量重置日，流量包不改
                    if ($goods->type == 2) {
                        // 将商品的有效期和流量自动重置日期加到账号上
                        $traffic_reset_day = in_array(date('d'), [29, 30, 31]) ? 28 : abs(date('d'));
                        User::query()->where('id', $order->user_id)->update(['traffic_reset_day' => $traffic_reset_day, 'expire_time' => date('Y-m-d', strtotime("+" . $goods->days . " days", strtotime($order->user->expire_time))), 'enable' => 1]);
                    } else {
                        // 将商品的有效期和流量自动重置日期加到账号上
                        User::query()->where('id', $order->user_id)->update(['expire_time' => date('Y-m-d', strtotime("+" . $goods->days . " days")), 'enable' => 1]);
                    }

                    // 写入用户标签
                    if ($goods->label) {
                        // 取出现有的标签
                        $userLabels = UserLabel::query()->where('user_id', $order->user_id)->pluck('label_id')->toArray();
                        $goodsLabels = GoodsLabel::query()->where('goods_id', $order->goods_id)->pluck('label_id')->toArray();
                        $newUserLabels = array_merge($userLabels, $goodsLabels);

                        // 删除用户所有标签
                        UserLabel::query()->where('user_id', $order->user_id)->delete();

                        // 生成标签
                        foreach ($newUserLabels as $vo) {
                            $obj = new UserLabel();
                            $obj->user_id = $order->user_id;
                            $obj->label_id = $vo;
                            $obj->save();
                        }
                    }

                    // 写入返利日志
                    if ($order->user->referral_uid) {
                        $referralLog = new ReferralLog();
                        $referralLog->user_id = $order->user_id;
                        $referralLog->ref_user_id = $order->user->referral_uid;
                        $referralLog->order_id = $order->oid;
                        $referralLog->amount = $order->amount;
                        $referralLog->ref_amount = $order->amount * self::$config['referral_percent'];
                        $referralLog->status = 0;
                        $referralLog->save();
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    Log::info('【有赞云】更新支付单和订单异常：' . $e->getMessage());
                }

                exit();
            }

            // 超时自动关闭订单
            if ($data['status'] == 'TRADE_CLOSED') {
                if ($payment->status != 0) {
                    Log::info('【有赞云】自动关闭支付单异常，本地支付单状态不正确');
                    exit();
                }

                $order = Order::query()->where('oid', $payment->oid)->first();
                if ($order->status != 0) {
                    Log::info('【有赞云】自动关闭支付单异常，本地订单状态不正确');
                    exit();
                }

                DB::beginTransaction();
                try {
                    // 关闭支付单
                    $payment->status = -1;
                    $payment->save();

                    // 关闭订单
                    $order->status = -1;
                    $order->save();

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::info('【有赞云】更新支付单和订单异常');
                }

                exit();
            }
        }

        if ($data['type'] == 'TRADE') {
            if ($data['status'] == 'WAIT_BUYER_PAY') {
                Log::info('【有赞云】等待支付' . urldecode($data['msg']));
                exit();
            }

            if ($data['status'] == 'TRADE_SUCCESS') {
                Log::info('【有赞云】支付成功' . urldecode($data['msg']));
                exit();
            }

            // 用户已签收
            if ($data['status'] == 'TRADE_BUYER_SIGNED') {
                Log::info('【有赞云】用户已签收' . urldecode($data['msg']));
                exit();
            }

            if ($data['status'] == 'TRADE_CLOSED') {
                Log::info('【有赞云】超时未支付自动支付' . urldecode($data['msg']));
                exit();
            }
        }

        exit();
    }

    public function show(Request $request)
    {
        exit('show');
    }

    // 写入回调请求日志
    private function callbackLog($client_id, $yz_id, $kdt_id, $kdt_name, $mode, $msg, $sendCount, $sign, $status, $test, $type, $version)
    {
        $paymentCallback = new PaymentCallback();
        $paymentCallback->client_id = $client_id;
        $paymentCallback->yz_id = $yz_id;
        $paymentCallback->kdt_id = $kdt_id;
        $paymentCallback->kdt_name = $kdt_name;
        $paymentCallback->mode = $mode;
        $paymentCallback->msg = urldecode($msg);
        $paymentCallback->sendCount = $sendCount;
        $paymentCallback->sign = $sign;
        $paymentCallback->status = $status;
        $paymentCallback->test = $test;
        $paymentCallback->type = $type;
        $paymentCallback->version = $version;
        $paymentCallback->save();

        return $paymentCallback->id;
    }
}
