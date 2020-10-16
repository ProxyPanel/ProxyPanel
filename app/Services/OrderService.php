<?php

namespace App\Services;

use App\Components\Helpers;
use App\Models\Order;
use App\Models\ReferralLog;
use App\Models\User;
use Log;

class OrderService
{
    public static $order;
    public static $user;
    public static $goods;
    public static $payment;

    public function __construct(Order $order)
    {
        // 获取需要的信息
        self::$order = $order;
        self::$user = $order->user;
        self::$goods = $order->goods;
        self::$payment = $order->payment;
    }

    // 支付成功后处理
    public function receivedPayment(): bool
    {
        if (self::$payment) {// 是否为余额购买套餐
            if (self::$payment->status === 1) {// 已处理
                return true;
            }
            self::$payment->update(['status' => 1]);

            // 余额充值
            if (self::$order->goods_id === 0 || self::$order->goods_id === null) {
                return $this->chargeCredit();
            }
        }

        $goods = self::$order->goods;
        switch ($goods->type) {// 商品为流量或者套餐
            case 1:// 流量包
                $this->activatePackage();
                break;
            case 2:// 套餐
                if (Order::userActivePlan(self::$user->id)->where('id', '<>', self::$order->id)->exists()) {// 判断套餐是否直接激活
                    $this->setPrepaidPlan();
                } else {
                    $this->activatePlan();
                }
                $this->setCommissionExpense(self::$user); // 返利
                break;
            default:
                Log::error('【处理订单】出现错误-未知套餐类型');
        }

        return true;
    }

    // 余额充值
    private function chargeCredit(): bool
    {
        $credit = self::$user->credit;
        $ret = (new UserService(self::$user))->updateCredit(self::$order->origin_amount);
        // 余额变动记录日志
        if ($ret) {
            Helpers::addUserCreditLog(
                self::$order->user_id,
                self::$order->id,
                $credit,
                self::$user->credit,
                self::$order->amount,
                '用户通过'.self::$order->pay_way.'充值余额'
            );
        }

        return $ret;
    }

    // 激活流量包
    private function activatePackage(): bool
    {
        $ret = (new UserService(self::$user))->incrementData(self::$goods->traffic * MB);
        if ($ret) {
            return Helpers::addUserTrafficModifyLog(
                self::$order->user_id,
                self::$order->id,
                self::$user->transfer_enable - self::$goods->traffic * MB,
                self::$user->transfer_enable,
                '['.self::$order->pay_way.']加上用户购买的套餐流量'
            );
        }

        return false;
    }

    // 设置预支付套餐
    private function setPrepaidPlan(): bool
    {
        self::$order->status = 3; // 3为预支付
        // 预支付订单, 刷新账号有效时间用于流量重置判断
        return self::$order->save()
            && self::$user->update(['expired_at' => date('Y-m-d', strtotime(self::$user->expired_at.' +'.self::$goods->days.' days'))]);
    }

    // 激活套餐
    private function activatePlan(): bool
    {
        Order::whereId(self::$order->id)->update(['expired_at' => date('Y-m-d H:i:s', strtotime('+'.self::$goods->days.' days'))]);
        $oldData = self::$user->transfer_enable;
        $updateData = [
            'invite_num' => self::$user->invite_num + (self::$goods->invite_num ?: 0),
            'level'      => self::$goods->level,
            'enable'     => 1,
        ];

        // 无端口用户 添加端口
        if (self::$user->port === null || self::$user->port === 0) {
            $updateData['port'] = Helpers::getPort();
        }

        $ret = self::$user->update(array_merge($this->resetTimeAndData(), $updateData));
        if ($ret) {
            return Helpers::addUserTrafficModifyLog(
                self::$order->user_id,
                self::$order->id,
                $oldData,
                self::$user->transfer_enable,
                '【'.self::$order->pay_way.'】加上用户购买的套餐流量'
            );
        }

        return false;
    }

    // 计算下次重置与账号过期时间
    public function resetTimeAndData($expired_at = null): array
    {
        $data = ['u' => 0, 'd' => 0];
        // 账号有效期
        if (!$expired_at) {
            $expired_at = date('Y-m-d', strtotime('+'.self::$goods->days.' days'));
            foreach (Order::userPrepay(self::$order->user_id)->get() as $paidOrder) {//拿出可能存在的其余套餐, 推算最新的到期时间
                //取出对应套餐信息
                $expired_at = date('Y-m-d', strtotime("$expired_at +".$paidOrder->goods->days.' days'));
            }
            $data['expired_at'] = $expired_at;
        }

        //账号流量重置日期
        $nextResetTime = date('Y-m-d', strtotime('+'.self::$goods->period.' days'));
        if ($nextResetTime >= $expired_at) {
            $nextResetTime = null;
        }

        return array_merge($data, [
            'transfer_enable' => self::$goods->traffic * MB,
            'reset_time'      => $nextResetTime,
        ]);
    }

    // 佣金计算
    private function setCommissionExpense(User $user): bool
    {
        $referralType = sysConfig('referral_type');

        if ($referralType && $user->inviter_id) {// 是否需要支付佣金
            $inviter = $user->inviter;
            // 获取历史返利记录
            $referral = ReferralLog::whereInviteeId(self::$order->user_id)->doesntExist();
            // 无记录 / 首次返利
            if ($referral && sysConfig('is_invite_register')) {
                // 邀请注册功能开启时，返还邀请者邀请名额
                $inviter->update(['invite_num' => $inviter->invite_num + 1]);
            }
            // 按照返利模式进行返利判断
            if ($referralType == 2 || $referral) {
                return $this->addReferralLog(
                    $user->id,
                    $inviter->id,
                    self::$order->id,
                    self::$order->amount,
                    self::$order->amount * sysConfig('referral_percent')
                );
            }
        }

        return true;
    }

    /**
     * 添加返利日志.
     *
     * @param int $inviteeId  用户ID
     * @param int $inviterId  返利对象ID
     * @param int $oid        订单ID
     * @param int $amount     发生金额
     * @param int $commission 返利金额
     *
     * @return bool
     */
    private function addReferralLog(int $inviteeId, int $inviterId, int $oid, int $amount, int $commission): bool
    {
        $log = new ReferralLog();
        $log->invitee_id = $inviteeId;
        $log->inviter_id = $inviterId;
        $log->order_id = $oid;
        $log->amount = $amount;
        $log->commission = $commission;
        $log->status = 0;

        return $log->save();
    }

    // 激活预支付套餐
    public function activatePrepaidPlan(): bool
    {
        self::$order->update([
            'expired_at' => date('Y-m-d H:i:s', strtotime('+'.self::$goods->days.' days')),
            'status'     => 2,
        ]);

        return $this->activatePlan();
    }
}
