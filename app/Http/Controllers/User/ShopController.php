<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Node;
use App\Models\Order;
use App\Services\CouponService;
use App\Utils\Helpers;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class ShopController extends Controller
{
    public function index(): View
    { // 商品列表
        $user = auth()->user();
        // 余额充值商品，只取10个
        $renewOrder = Order::userActivePlan($user->id)->first();
        $renewPrice = $renewOrder->goods->renew ?? 0;
        // 有重置日时按照重置日为标准，否则就以过期日为标准
        $dataPlusDays = $user->reset_time ?? $user->expired_at;

        $goodsList = Goods::whereStatus(1)->where('type', '<=', '2')->orderByDesc('type')->orderByDesc('sort')->get();

        if ($user && $nodes = $user->userGroup) {
            $nodes = $nodes->nodes();
        } else {
            $nodes = Node::all();
        }
        foreach ($goodsList as $goods) {
            $goods->node_count = $nodes->where('level', '<=', $goods->level)->where('status', 1)->count();
            $goods->node_countries = $nodes->where('level', '<=', $goods->level)->where('status', 1)->pluck('country_code')->unique();
        }

        return view('user.services', [
            'chargeGoodsList' => Goods::type(3)->orderBy('price')->get(),
            'goodsList' => $goodsList,
            'renewTraffic' => $renewPrice ? Helpers::getPriceTag($renewPrice) : 0,
            'dataPlusDays' => $dataPlusDays > date('Y-m-d') ? $dataPlusDays->diffInDays() : 0,
        ]);
    }

    public function resetTraffic(): JsonResponse
    { // 重置流量
        $user = auth()->user();
        $order = Order::userActivePlan()->firstOrFail();
        $renewCost = $order->goods->renew;
        if ($user->credit < $renewCost) {
            return response()->json(['status' => 'fail', 'message' => trans('user.payment.insufficient_balance')]);
        }

        $user->update(['u' => 0, 'd' => 0]);

        // 记录余额操作日志
        Helpers::addUserCreditLog($user->id, null, $user->credit, $user->credit - $renewCost, -1 * $renewCost, 'The user manually reset the data.');

        // 扣余额
        $user->updateCredit(-$renewCost);

        return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.reset')])]);
    }

    public function checkBonus(Request $request, Goods $good): JsonResponse
    { // 兑换优惠券码
        $coupon_sn = $request->input('coupon_sn');

        if (empty($coupon_sn)) {
            return response()->json(['status' => 'fail', 'title' => trans('common.failed'), 'message' => trans('user.coupon.error.unknown')]);
        }

        $coupon = (new CouponService($coupon_sn))->search($good); // 检查券合规性

        if (! $coupon instanceof Coupon) {
            return $coupon;
        }

        $data = [
            'name' => $coupon->name,
            'type' => $coupon->type,
            'value' => $coupon->type === 2 ? $coupon->value : Helpers::getPriceTag($coupon->value),
        ];

        return response()->json(['status' => 'success', 'data' => $data, 'message' => trans('common.applied', ['attribute' => trans('model.coupon.attribute')])]);
    }

    public function show(Goods $good): View
    { // 显示服务详细
        $user = auth()->user();
        // 有重置日时按照重置日为标准，否则就以过期日为标准
        $dataPlusDays = $user->reset_time ?? $user->expired_at;

        return view('user.buy', [
            'dataPlusDays' => $dataPlusDays > date('Y-m-d') ? $dataPlusDays->diffInDays() : 0,
            'activePlan' => Order::userActivePlan()->exists(),
            'goods' => $good,
        ]);
    }

    public function charge(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'coupon_sn' => [
                'required', Rule::exists('coupon', 'sn')->where(static function ($query) {
                    $query->whereType(3)->whereStatus(0);
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        if ((new CouponService($request->input('coupon_sn')))->charge()) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('user.recharge')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('user.recharge')])]);
    }
}
