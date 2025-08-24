<?php

namespace App\Services;

use App\Models\Goods;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReferralLog;
use App\Models\User;
use App\Utils\Helpers;
use Log;

class OrderService
{
    public static User $user;

    public static ?Goods $goods;

    public static ?Payment $payment;

    public function __construct(private readonly Order $order)
    { // 获取需要的信息
        self::$user = $order->user;
        self::$goods = $order->goods;
        self::$payment = $order->payment;
    }

    public function receivedPayment(): bool
    { // 支付成功后处理
        $payment = self::$payment;
        if ($payment && $payment->status !== 1) {// 是否为余额购买套餐
            $payment->complete();
        }

        $goods = self::$goods;
        if ($goods === null) {
            $ret = $this->chargeCredit();
        } else {
            switch ($goods->type) {// 商品为流量或者套餐
                case 1: // 流量包
                    $ret = $this->activatePackage();
                    break;
                case 2: // 套餐
                    if (Order::userActivePlan(self::$user->id)->where('id', '<>', $this->order->id)->exists()) {// 判断套餐是否直接激活
                        $ret = $this->order->prepay();
                    } else {
                        $ret = $this->activatePlan();
                    }
                    $this->setCommissionExpense(self::$user); // 返利
                    break;
                default:
                    Log::emergency('【处理订单】出现错误-未知套餐类型');
            }
        }

        return $ret ?? true;
    }

    private function chargeCredit(): bool
    { // 余额充值
        $credit = self::$user->credit;
        $ret = self::$user->updateCredit($this->order->origin_amount);
        // 余额变动记录日志
        if ($ret) {
            Helpers::addUserCreditLog($this->order->user_id, $this->order->id, $credit, self::$user->credit, $this->order->amount, 'The user topped up the balance.');
        }

        return $ret;
    }

    private function activatePackage(): bool
    { // 激活流量包
        if (self::$user->incrementData(self::$goods->traffic * MiB)) {
            return Helpers::addUserTrafficModifyLog($this->order->user_id, self::$user->transfer_enable - self::$goods->traffic * MiB, self::$user->transfer_enable, trans("[:payment] plus the user's purchased data plan.", ['payment' => $this->order->pay_way]));
        }

        return false;
    }

    public function activatePlan(): bool
    { // 激活套餐
        $this->order->refresh()->update(['expired_at' => date('Y-m-d H:i:s', strtotime(self::$goods->days.' days'))]);
        $oldData = self::$user->transfer_enable;
        $updateData = [
            'invite_num' => self::$user->invite_num + (self::$goods->invite_num ?: 0),
            'level' => self::$goods->level,
            'speed_limit' => self::$goods->speed_limit,
            'enable' => 1,
            ...$this->resetTimeAndData(),
        ];

        // 无端口用户 添加端口
        if (empty(self::$user->port)) {
            $updateData['port'] = Helpers::getPort();
        }

        if (self::$user->update($updateData)) {
            return Helpers::addUserTrafficModifyLog($this->order->user_id, $oldData, self::$user->transfer_enable, trans("[:payment] plus the user's purchased data plan.", ['payment' => $this->order->pay_way]), $this->order->id);
        }

        return false;
    }

    public function resetTimeAndData(?string $expired_at = null): array
    { // 计算下次重置与账号过期时间
        if (! $expired_at) { // 账号有效期
            $expired_at = $this->getFinallyExpiredTime();
        }

        // 账号流量重置日期
        $nextResetTime = now()->addDays(self::$goods->period)->toDateString();
        if ($nextResetTime >= $expired_at) {
            $nextResetTime = null;
        }

        return [
            'u' => 0,
            'd' => 0,
            'transfer_enable' => self::$goods->traffic * MiB,
            'expired_at' => $expired_at,
            'reset_time' => $nextResetTime,
        ];
    }

    private function getFinallyExpiredTime(): string
    { // 推算最新的到期时间
        $orders = self::$user->orders()->whereIn('status', [2, 3])->whereIsExpire(0)->isPlan()->get();
        $current = $orders->where('status', '==', 2)->first();

        return ($current->expired_at ?? now())->addDays($orders->except($current->id ?? 0)->sum('goods.days'))->toDateString();
    }

    private function setCommissionExpense(User $user): void
    { // 佣金计算
        $referralType = sysConfig('referral_reward_type');

        if ($referralType && $user->inviter_id) {// 是否需要支付佣金
            $inviter = $user->inviter;
            // 获取历史返利记录
            $referral = ReferralLog::whereInviteeId($user->id)->doesntExist();
            // 无记录 / 首次返利
            if ($referral && sysConfig('is_invite_register')) {
                // 邀请注册功能开启时，返还邀请者邀请名额
                $inviter->increment('invite_num');
            }
            // 按照返利模式进行返利判断
            if ($referralType === '2' || $referral) {
                $inviter->commissionLogs()
                    ->create([
                        'invitee_id' => $user->id,
                        'order_id' => $this->order->id,
                        'amount' => $this->order->amount,
                        'commission' => $this->order->amount * sysConfig('referral_percent'),
                    ]);
            }
        }
    }

    public function refreshAccountExpiration(): bool
    { // 刷新账号有效时间
        $data = ['expired_at' => $this->getFinallyExpiredTime()];

        if ($data['expired_at'] < now()->toDateString()) {
            $data += [
                'u' => 0,
                'd' => 0,
                'transfer_enable' => 0,
                'enable' => 0,
                'level' => 0,
                'reset_time' => null,
                'ban_time' => null,
            ];
        }

        return self::$user->update($data);
    }

    public function activatePrepaidPlan(): bool
    { // 激活预支付套餐
        $this->order->complete();

        return $this->activatePlan();
    }
}
