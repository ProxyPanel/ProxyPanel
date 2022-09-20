<?php

namespace App\Services;

use App\Components\Helpers;
use App\Models\Coupon;
use App\Models\Goods;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Response;

class CouponService
{
    public $code;
    public $user;

    public function __construct(string $code)
    {
        $this->code = $code;
        $this->user = Auth::getUser();
    }

    public function search(Goods $goods) // 寻找合适的券
    {
        $coupons = Coupon::whereSn($this->code)->whereIn('type', [1, 2])->orderByDesc('priority')->get();
        if ($coupons->isNotEmpty()) {
            foreach ($coupons as $coupon) {
                $ret = $this->check($goods, $coupon);
                if ($ret === true) { // passed
                    return $coupon;
                }
            }

            return $ret ?? $this->failedReturn(trans('common.failed'), trans('user.coupon.error.unknown'));
        }

        return $this->failedReturn(trans('common.failed'), trans('user.coupon.error.unknown'));
    }

    private function check(Goods $goods, Coupon $coupon) // 检查券合规性
    {
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

        if ($coupon->status === 2) {
            return $this->failedReturn(trans('common.sorry'), trans('user.coupon.error.expired'));
        }

        if (time() < $coupon->getRawOriginal('start_time')) {
            return $this->failedReturn(trans('user.coupon.error.inactive'), trans('user.coupon.error.wait', ['time' => $coupon->start_time]));
        }

        if (isset($coupon->limit['minimum']) && $goods->price < $coupon->limit['minimum']) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.minimum', ['amount' => $coupon->limit['minimum']]));
        }

        if (isset($coupon->limit['users']['black']) && in_array($this->user->id, $coupon->limit['users']['black'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
        }

        if (isset($coupon->limit['services']['black']) && in_array($goods->id, $coupon->limit['services']['black'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.services'));
        }

        if (isset($coupon->limit['users']['white']) && ! in_array($this->user->id, $coupon->limit['users']['white'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
        }

        if (isset($coupon->limit['services']['white']) && ! in_array($goods->id, $coupon->limit['services']['white'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.services'));
        }

        if (isset($coupon->limit['users']['levels']) && ! in_array($this->user->level, $coupon->limit['users']['levels'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
        }

        if (isset($coupon->limit['users']['groups']) && ! in_array($this->user->user_group_id, $coupon->limit['users']['groups'], true)) {
            return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
        }

        if (isset($coupon->limit['users']['newbie'])) { // 新用户可用
            if (isset($coupon->limit['users']['newbie']['coupon']) && $this->user->orders()->whereNotNull('coupon_id')->exists()) { // 第一次使用优惠券
                return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
            }

            if (isset($coupon->limit['users']['newbie']['order']) && $this->user->orders()->exists()) { // 第一个套餐订单

                return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
            }

            if (isset($coupon->limit['users']['newbie']['days']) && (time() > strtotime($this->user->created_at.' +'.$coupon->limit['users']['newbie']['days'].' days') ||
                    $this->user->orders()->whereCouponId($coupon->id)->exists())) { // 创号N天内, 且第一次用优惠券

                return $this->failedReturn(trans('user.coupon.error.unmet'), trans('user.coupon.error.users'));
            }
        }

        if (isset($coupon->limit['used']) && $this->user->orders()->whereCouponId($coupon->id)->count() >= $coupon->limit['used']) {
            return $this->failedReturn(trans('user.coupon.error.unmet'),
                trans_choice('user.coupon.error.overused', $coupon->limit['used'], ['times' => $coupon->limit['used']]));
        }

        return true;
    }

    private function failedReturn(string $title, string $message)
    {
        return Response::json(['status' => 'fail', 'title' => $title, 'message' => $message]);
    }

    public function charge(): bool
    {
        $coupon = Coupon::whereSn($this->code)->whereType(3)->first();
        if ($coupon && $coupon->status === 0) {
            try {
                Helpers::addUserCreditLog($this->user->id, null, $this->user->credit, $this->user->credit + $coupon->value, $coupon->value,
                    trans('user.recharge').' - ['.trans('user.coupon.recharge').'：'.$coupon->sn.']'); // 写入用户余额变动日志
                $this->user->updateCredit($coupon->value); // 余额充值
                $coupon->used(); // 更改卡券状态
                Helpers::addCouponLog(trans('user.recharge_credit'), $coupon->id); // 写入卡券使用日志

                return true;
            } catch (Exception $exception) {
                Log::emergency('[重置券处理出现错误] '.$exception->getMessage());
            }
        }

        return false;
    }
}
