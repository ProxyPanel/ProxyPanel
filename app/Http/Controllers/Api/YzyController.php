<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Models\Coupon;
use App\Http\Models\CouponLog;
use App\Http\Models\Goods;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\ReferralLog;
use App\Http\Models\User;
use Illuminate\Http\Request;
use Response;
use Redirect;
use Cache;
use DB;
use Log;

/**
 * 有赞云支付
 * Class YzyController
 *
 * @package App\Http\Controllers
 */
class YzyController extends Controller
{
    protected static $config;
    private $accessToken;

    function __construct()
    {
        self::$config = $this->systemConfig();
        $this->accessToken = $this->getAccessToken();
    }

    // 获取accessToken
    private function getAccessToken()
    {
        if (Cache::has('YZY_TOKEN')) {
            return Cache::get('YZY_TOKEN')['access_token'];
        }

        $clientId = self::$config['youzan_client_id']; // f531e5282e4689712a
        $clientSecret = self::$config['youzan_client_secret']; // 4020b1743633ef334fd06a32190ee677

        $type = 'self';
        $keys['kdt_id'] = self::$config['kdt_id']; // 40503761

        $token = (new \Youzan\Open\Token($clientId, $clientSecret))->getToken($type, $keys);

        Cache::put('YZY_TOKEN', $token, 10000);

        return $token['access_token'];
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

        // 判断消息是否合法
        $msg = $data['msg'];
        $sign_string = self::$config['youzan_client_id'] . "" . $msg . "" . self::$config['youzan_client_secret'];
        $sign = md5($sign_string);
        if ($sign != $data['sign']) {
            exit();
        } else {
            // msg内容经过 urlencode 编码，需进行解码
            $msg = json_decode(urldecode($msg), true);

            if ($data['type'] == 'TRADE_ORDER_STATE' && $data['status'] == 'TRADE_SUCCESS') {
                $client = new \Youzan\Open\Client($this->accessToken);

                $method = 'youzan.trade.get';
                $apiVersion = '3.0.0';
                $params = [
                    'tid' => $msg['tid'], // 有赞订单号
                ];

                $result = $client->post($method, $apiVersion, $params);
                if (isset($result['error_response'])) {
                    Log::info('【有赞云】回调订单信息错误：' . $result['error_response']['msg']);

                    return Response::json(['code' => 0, 'msg' => 'success']);
                }

                // 处理订单&支付单
                $payment = Payment::query()->where('qr_id', $result['response']['trade']['qr_id'])->first();
                if (!$payment) {
                    Log::info('【有赞云】回调订单不存在');

                    return Response::json(['code' => 0, 'msg' => 'success']);
                }

                if ($payment->status != '0') {
                    Log::info('【有赞云】回调订单状态不正确');

                    return Response::json(['code' => 0, 'msg' => 'success']);
                }

                DB::beginTransaction();
                try {
                    // 更新支付单
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

                    // 套餐就改流量重置日，加油包不改
                    if ($goods->type == 2) {
                        // 将商品的有效期和流量自动重置日期加到账号上
                        $traffic_reset_day = in_array(date('d'), [29, 30, 31]) ? 28 : abs(date('d'));
                        User::query()->where('id', $order->user_id)->update(['traffic_reset_day' => $traffic_reset_day, 'expire_time' => date('Y-m-d', strtotime("+" . $goods->days . " days", strtotime($order->user->expire_time))), 'enable' => 1]);
                    } else {
                        // 将商品的有效期和流量自动重置日期加到账号上
                        User::query()->where('id', $order->user_id)->update(['expire_time' => date('Y-m-d', strtotime("+" . $goods->days . " days")), 'enable' => 1]);
                    }

                    // 写入返利日志
                    if ($order->user->referral_uid) {
                        $referralLog = new ReferralLog();
                        $referralLog->user_id = $order->user_id;
                        $referralLog->ref_user_id = $order->user->referral_uid;
                        $referralLog->order_id = $order->oid;
                        $referralLog->amount = $order->totalPrice;
                        $referralLog->ref_amount = $order->totalPrice * self::$config['referral_percent'];
                        $referralLog->status = 0;
                        $referralLog->save();
                    }

                    DB::commit();

                    return Response::json(['code' => 0, 'msg' => 'success']);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::info('【有赞云】更新支付单和订单异常');

                    return Response::json(['code' => 0, 'msg' => 'success']);
                }
            }

            return Response::json(['code' => 0, 'msg' => 'success']);
        }
    }

    public function show(Request $request)
    {
        exit('show');
    }
}
