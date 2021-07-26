<?php

namespace App\Http\Controllers\Api\Client;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\GoodsCategory;
use App\Models\Order;
use App\Models\ReferralLog;
use App\Models\User;
use Exception;
use Hashids\Hashids;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Log;
use Validator;

class V1Controller extends Controller
{
    private static $method;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'shop', 'config', 'getConfig']]);
        auth()->shouldUse('api');
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public static function getStatus(Request $request): JsonResponse
    {
        $order_id = $request->input('order_id');
        $payment = Order::query()->find($order_id)->payment;
        if ($payment) {
            if ($payment->status === 1) {
                return response()->json(['ret' => 1, 'msg' => '支付成功']);
            }

            if ($payment->status === -1) {
                return response()->json(['ret' => 0, 'msg' => '订单超时未支付，已自动关闭']);
            }

            return response()->json(['ret' => 0, 'msg' => '等待支付']);
        }

        return response()->json(['ret' => 0, 'msg' => '未知订单']);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|'.(sysConfig('username_type') ?? 'email'),
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['ret' => 0, 'msg' => $validator->errors()->all()], 422);
        }

        if ($token = auth()->attempt($validator->validated())) {
            return $this->createNewToken($token);
        }

        return response()->json(['ret' => 0, 'msg' => '登录信息错误'], 401);
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'ret'  => 1,
            'data' => [
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth()->factory()->getTTL() * 60,
                'user'         => auth()->user()->profile(),
            ],
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|between:2,100',
            'username' => 'required|'.(sysConfig('username_type') ?? 'email').'|max:100|unique:user,username',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => $request->password]
        ));

        return response()->json(['ret' => 1, 'user' => $user], 201);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['ret' => 1]);
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile()
    {
        $user = auth()->user();
        $userInfo = $user->profile();
        $userInfo['subUrl'] = $user->subUrl();
        $totalTransfer = $user->transfer_enable;
        $usedTransfer = $user->used_traffic;
        $unusedTraffic = $totalTransfer - $usedTransfer > 0 ? $totalTransfer - $usedTransfer : 0;
        $userInfo['unusedTraffic'] = flowAutoShow($unusedTraffic);

        return response()->json(['ret' => 1, 'data' => $userInfo]);
    }

    public function nodeList(int $id = null)
    {
        $user = auth()->user();
        $nodes = $user->nodes()->get();

        return response()->json(['ret' => 1, 'data' => $nodes]);
    }

    public function shop()
    {
        $shops = [
            'keys' => [],
            'data' => [],
        ];
        foreach (GoodsCategory::query()->whereStatus(1)->get() as $item) {
            $shops['keys'][] = $item['name'];
            $shops['data'][$item['name']] = $item->goods()->get()->append('traffic_label')->toArray();
        }

        return response()->json(['ret' => 1, 'data' => $shops]);
    }

    public function getConfig()
    {
        $config = config('bobclient');
        $config['website_name'] = sysConfig('website_name');
        $config['website_url'] = sysConfig('website_url');
        $config['payment'] = [
            'alipay' => sysConfig('is_AliPay'),
            'wechat' => sysConfig('is_WeChatPay'),
        ];

        return response()->json(['ret' => 1, 'data' => $config]);
    }

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
                return response()->json(['ret' => 0, 'msg' => trans('user.payment.error')]);
            }
            $amount = $credit;
        // 购买服务
        } elseif ($goods_id && self::$method) {
            $goods = Goods::find($goods_id);
            if (! $goods || ! $goods->status) {
                return response()->json(['ret' => 0, 'msg' => '订单创建失败：商品已下架']);
            }
            $amount = $goods->price;

            // 是否有生效的套餐
            $activePlan = Order::userActivePlan()->doesntExist();

            //　无生效套餐，禁止购买加油包
            if ($goods->type === 1 && $activePlan) {
                return response()->json(['ret' => 0, 'msg' => '购买加油包前，请先购买套餐']);
            }

            // 单个商品限购
            if ($goods->limit_num) {
                $count = Order::uid()->where('status', '>=', 0)->whereGoodsId($goods_id)->count();
                if ($count >= $goods->limit_num) {
                    return response()->json(['ret' => 0, 'msg' => '此商品限购'.$goods->limit_num.'次，您已购买'.$count.'次']);
                }
            }

            // 使用优惠券
            if ($coupon_sn) {
                $coupon = Coupon::whereStatus(0)->whereIn('type', [1, 2])->whereSn($coupon_sn)->first();
                if (! $coupon) {
                    return response()->json(['ret' => 0, 'msg' => '订单创建失败：优惠券不存在']);
                }

                // 计算实际应支付总价
                $amount = $coupon->type === 2 ? $goods->price * $coupon->value / 100 : $goods->price - $coupon->value;
                $amount = $amount > 0 ? round($amount, 2) : 0; // 四舍五入保留2位小数，避免无法正常创建订单
            }

            //非余额付款下，检查在线支付是否开启
            if (self::$method !== 'credit') {
                // 判断是否开启在线支付
                if (! sysConfig('is_onlinePay')) {
                    return response()->json(['ret' => 0, 'msg' => '订单创建失败：系统并未开启在线支付功能']);
                }

                // 判断是否存在同个商品的未支付订单
                if (Order::uid()->whereStatus(0)->exists()) {
                    return response()->json(['ret' => 0, 'msg' => '订单创建失败：尚有未支付的订单，请先去支付']);
                }
            } elseif (Auth::getUser()->credit < $amount) { // 验证账号余额是否充足
                return response()->json(['ret' => 0, 'msg' => '您的余额不足，请先充值']);
            }

            // 价格异常判断
            if ($amount < 0) {
                return response()->json(['ret' => 0, 'msg' => '订单创建失败：订单总价异常']);
            }

            if ($amount === 0 && self::$method !== 'credit') {
                return response()->json(['ret' => 0, 'msg' => '订单创建失败：订单总价为0，无需使用在线支付']);
            }
        }

        // 生成订单
        try {
            $newOrder = Order::create([
                'sn'            => date('ymdHis').random_int(100000, 999999),
                'user_id'       => auth()->id(),
                'goods_id'      => $credit ? null : $goods_id,
                'coupon_id'     => $coupon->id ?? null,
                'origin_amount' => $credit ?: ($goods->price ?? 0),
                'amount'        => $amount,
                'pay_type'      => $pay_type,
                'pay_way'       => self::$method,
            ]);

            // 使用优惠券，减少可使用次数
            if (! empty($coupon)) {
                if ($coupon->usable_times > 0) {
                    $coupon->decrement('usable_times', 1);
                }

                Helpers::addCouponLog('订单支付使用', $coupon->id, $goods_id, $newOrder->id);
            }

            $request->merge(['id' => $newOrder->id, 'type' => $pay_type, 'amount' => $amount]);
            PaymentController::$method = self::$method;
            // 生成支付单
            $data = PaymentController::getClient()->purchase($request);
            $data = $data->getData(true);
            $data['order_id'] = $newOrder->id;

            return response()->json($data);
        } catch (Exception $e) {
            Log::error('订单生成错误：'.$e->getMessage());
        }

        return response()->json(['ret' => 0, 'msg' => '订单创建失败']);
    }

    public function gift(Request $request)
    {
        $user = $request->user('api');
        $referral_traffic = flowAutoShow(sysConfig('referral_traffic') * MB);
        $referral_percent = sysConfig('referral_percent');
        // 邀请码
        $code = $user->invites()->whereStatus(1)->value('code');

        $data['invite_gift'] = trans('user.invite.promotion', [
            'traffic'          => $referral_traffic,
            'referral_percent' => $referral_percent * 100,
        ]);
        $affSalt = sysConfig('aff_salt');
        if (isset($affSalt)) {
            $aff_link = route('register', ['aff' => (new Hashids($affSalt, 8))->encode($user->id)]);
        } else {
            $aff_link = route('register', ['aff' => $user->id]);
        }
        $data['invite_url'] = $aff_link;
        $data['invite_text'] = $aff_link.'&(复制整段文字到浏览器打开即可访问),找梯子最重要的就是稳定,这个已经上线三年了,一直稳定没有被封过,赶紧下载备用吧!安装后打开填写我的邀请码【'.$code.'】,你还能多得3天会员.';
        // 累计数据
        $data['back_sum'] = ReferralLog::query()->where('inviter_id', $user->id)->sum('commission') / 100;
        $data['user_sum'] = $user->invitees()->count();
        $data['list'] = $user->invitees()->selectRaw('username, UNIX_TIMESTAMP(created_at) as created_at')->limit(10)->get();

        return response()->json(['ret' => 1, 'data' => $data]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        // 系统开启登录加积分功能才可以签到
        if (! sysConfig('is_checkin')) {
            return response()->json(['ret' => 0, 'title' => trans('common.failed'), 'msg' => trans('user.home.attendance.disable')]);
        }

        // 已签到过，验证是否有效
        if (Cache::has('userCheckIn_'.$user->id)) {
            return response()->json(['ret' => 0, 'title' => trans('common.success'), 'msg' => trans('user.home.attendance.done')]);
        }

        $traffic = random_int((int) sysConfig('min_rand_traffic'), (int) sysConfig('max_rand_traffic')) * MB;

        if (! $user->incrementData($traffic)) {
            return response()->json(['ret' => 0, 'title' => trans('common.failed'), 'msg' => trans('user.home.attendance.failed')]);
        }

        // 写入用户流量变动记录
        Helpers::addUserTrafficModifyLog($user->id, null, $user->transfer_enable, $user->transfer_enable + $traffic, trans('user.home.attendance.attribute'));

        // 多久后可以再签到
        $ttl = sysConfig('traffic_limit_time') ? sysConfig('traffic_limit_time') * Minute : Day;
        Cache::put('userCheckIn_'.$user->id, '1', $ttl);

        return response()->json(['ret' => 1, 'msg' => trans('user.home.attendance.success', ['data' => flowAutoShow($traffic)])]);
    }
}
