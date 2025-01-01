<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Goods;
use App\Models\User;
use App\Utils\Helpers;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Response;

class CouponService
{
    public User $user;

    public function __construct(private readonly string $code)
    {
        $this->user = auth()->user();
    }

    public function search(Goods $goods): JsonResponse|Coupon
    { // 寻找合适的券
        $coupons = Coupon::whereSn($this->code)->whereIn('type', [1, 2])->orderByDesc('priority')->get();
        if ($coupons->isNotEmpty()) {
            foreach ($coupons as $coupon) {
                $ret = $this->check($goods, $coupon);
                if ($ret === true) { // passed
                    return $coupon;
                }
            }
        }

        return $ret ?? $this->failedReturn(trans('common.failed'), trans('user.coupon.error.unknown'));
    }

    private function check(Goods $goods, Coupon $coupon): JsonResponse|bool
    { // 检查券合规性
        $user = $this->user;
        if ($coupon->status === 2) {
            return $this->failedReturn(trans('common.sorry'), trans('user.coupon.error.expired'));
        }

        if ($coupon->status === 1) {
            return $this->failedReturn(trans('common.sorry'), trans('user.coupon.error.used'));
        }

        if (time() > $coupon->getRawOriginal('end_time')) {
            $coupon->expired();

            return $this->failedReturn(trans('common.sorry'), trans('user.coupon.error.expired'));
        }

        if ($coupon->usable_times === 0) {
            return $this->failedReturn(trans('common.sorry'), trans('user.coupon.error.run_out'));
        }

        if (time() < $coupon->getRawOriginal('start_time')) {
            return $this->failedReturn(trans('user.coupon.error.inactive'), trans('user.coupon.error.wait', ['time' => $coupon->start_time]));
        }

        if (isset($coupon->limit['minimum']) && $goods->price < $coupon->limit['minimum']) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.minimum', ['amount' => Helpers::getPriceTag($coupon->limit['minimum'])]));
        }

        if (isset($coupon->limit['users']['black']) && in_array($user->id, $coupon->limit['users']['black'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
        }

        if (isset($coupon->limit['services']['black']) && in_array($goods->id, $coupon->limit['services']['black'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.services'));
        }

        if (isset($coupon->limit['users']['white']) && ! in_array($user->id, $coupon->limit['users']['white'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
        }

        if (isset($coupon->limit['services']['white']) && ! in_array($goods->id, $coupon->limit['services']['white'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.services'));
        }

        if (isset($coupon->limit['users']['levels']) && ! in_array($user->level, $coupon->limit['users']['levels'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
        }

        if (isset($coupon->limit['users']['groups']) && ! in_array($user->user_group_id, $coupon->limit['users']['groups'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
        }

        if (isset($coupon->limit['users']['newbie'])) { // 新用户可用
            if (isset($coupon->limit['users']['newbie']['coupon']) && $user->orders()->whereNotNull('coupon_id')->exists()) { // 第一次使用优惠券
                return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
            }

            if (isset($coupon->limit['users']['newbie']['order']) && $user->orders()->whereIn('status', [2, 3])->exists()) { // 第一个支付过的订单
                return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
            }

            if (isset($coupon->limit['users']['newbie']['days']) && (time() > strtotime($user->created_at.' +'.$coupon->limit['users']['newbie']['days'].' days') ||
                    $user->orders()->whereCouponId($coupon->id)->exists())) { // 创号N天内, 且第一次用优惠券
                return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
            }
        }

        if (isset($coupon->limit['used']) && $user->orders()->whereCouponId($coupon->id)->count() >= $coupon->limit['used']) {
            return $this->failedReturn(trans('user.coupon.error.unmet'),
                trans_choice('user.coupon.error.overused', $coupon->limit['used'], ['times' => $coupon->limit['used']]));
        }

        return true;
    }

    private function failedReturn(string $title, string $message): JsonResponse
    {
        return Response::json(['status' => 'fail', 'title' => $title, 'message' => $message]);
    }

    public function charge(): bool
    {
        $user = $this->user;
        $coupon = Coupon::whereSn($this->code)->whereType(3)->first();
        if ($coupon && $coupon->status === 0) {
            try {
                $user->updateCredit($coupon->value); // 余额充值
                Helpers::addUserCreditLog($user->id, null, $user->credit, $user->credit + $coupon->value, $coupon->value, 'Recharge using a recharge voucher.'); // 写入用户余额变动日志

                $coupon->used(); // 更改卡券状态
                Helpers::addCouponLog('Used for credit recharge.', $coupon->id); // 写入卡券使用日志

                return true;
            } catch (Exception $exception) {
                Log::emergency(trans('common.error_action_item', ['action' => trans('common.apply'), 'attribute' => trans('model.coupon.attribute')]).': '.$exception->getMessage());
            }
        }

        return false;
    }
}
