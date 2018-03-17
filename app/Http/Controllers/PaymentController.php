<?php
namespace App\Http\Controllers;

use App\Http\Models\Coupon;
use App\Http\Models\CouponLog;
use App\Http\Models\Goods;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\User;
use Illuminate\Http\Request;
use Response;
use Redirect;
use Cache;
use Log;
use DB;

class PaymentController extends Controller
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

    // 创建支付单
    public function create(Request $request)
    {
        $goods_id = intval($request->get('goods_id'));
        $coupon_sn = $request->get('coupon_sn');

        $goods = Goods::query()->where('id', $goods_id)->where('status', 1)->first();
        if (!$goods) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：商品或服务已下架']);
        }

        // 使用优惠券
        if ($coupon_sn) {
            $coupon = Coupon::query()->where('sn', $coupon_sn)->where('is_del', 0)->where('status', 0)->first();
            if (!$coupon) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：优惠券不存在']);
            }

            // 计算实际应支付总价
            $totalPrice = $coupon->type == 2 ? $goods->price * $coupon->discount : $goods->price - $coupon->amount;
            $totalPrice = $totalPrice > 0 ? $totalPrice : 0;
        } else {
            $totalPrice = $goods->price;
        }

        DB::beginTransaction();
        try {
            $user = $request->session()->get('user');
            $orderId = date('ymdHis') . mt_rand(100000, 999999);
            $sn = makeRandStr(12);

            // 生成订单
            $order = new Order();
            $order->orderId = $orderId;
            $order->user_id = $user['id'];
            $order->goods_id = $goods_id;
            $order->coupon_id = !empty($coupon) ? $coupon->id : 0;
            $order->totalOriginalPrice = $goods->price;
            $order->totalPrice = $totalPrice;
            $order->expire_at = date("Y-m-d H:i:s", strtotime("+" . $goods->days . " days"));
            $order->is_expire = 0;
            $order->pay_way = 2;
            $order->status = 0;
            $order->save();

            // 生成支付单
            $client = new \Youzan\Open\Client($this->accessToken);

            $method = 'youzan.pay.qrcode.create';
            $apiVersion = '3.0.0';

            $params = [
                'qr_name'   => $goods->name, // 商品名
                'qr_price'  => $totalPrice, // 单位分
                'qr_source' => $orderId, // 本地订单号
                'qr_type'   => 'QR_TYPE_DYNAMIC'
            ];

            $result = $client->get($method, $apiVersion, $params);
            if (isset($result['error_response'])) {
                Log::error('【有赞云】创建二维码失败：' . $result['error_response']['msg']);

                throw new \Exception($result['error_response']['msg']);
            }

            $payment = new Payment();
            $payment->sn = $sn;
            $payment->user_id = $user['id'];
            $payment->oid = $order->oid;
            $payment->orderId = $orderId;
            $payment->pay_way = 1;
            $payment->amount = $order->totalPrice;
            $payment->qr_id = $result['response']['qr_id'];
            $payment->qr_url = $result['response']['qr_url'];
            $payment->qr_code = $result['response']['qr_code'];
            $payment->status = 0;
            $payment->save();

            DB::commit();

            return Response::json(['status' => 'success', 'data' => $sn, 'message' => '创建支付单成功']);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('创建支付订单失败：' . $e->getMessage());

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '创建支付单失败：' . $e->getMessage()]);
        }
    }

    // 支付单详情
    public function detail(Request $request, $sn)
    {
        if (empty($sn)) {
            return Redirect::to('user/goodsList');
        }

        $user = $request->session()->get('user');

        $payment = Payment::query()->with(['order', 'order.goods'])->where('sn', $sn)->where('user_id', $user['id'])->first();
        if (!$payment) {
            return Redirect::to('user/goodsList');
        }

        $order = Order::query()->where('oid', $payment->oid)->first();
        if (!$order) {
            $request->session()->flash('errorMsg', '订单不存在');

            return Response::view('payment/' . $sn);
        }

        $view['payment'] = $payment;

        return Response::view('payment/detail', $view);
    }

    // 获取订单支付状态
    public function getStatus(Request $request)
    {
        $sn = $request->get('sn');

        if (empty($sn)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '请求失败']);
        }

        $user = $request->session()->get('user');
        $payment = Payment::query()->where('sn', $sn)->where('user_id', $user['id'])->first();
        if (!$payment) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '支付失败']);
        }

        if ($payment->status) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '支付成功']);
        } else if ($payment->status < 0) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '支付失败']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等待支付']);
        }
    }
}