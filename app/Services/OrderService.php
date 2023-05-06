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

    public function receivedPayment(): bool
    { // 支付成功后处理
        if (self::$payment && self::$payment->status !== 1) {// 是否为余额购买套餐
            self::$payment->complete();
        }

        if (self::$order->goods_id === null) {
            $ret = $this->chargeCredit();
        } else {
            $goods = self::$order->goods;
            switch ($goods->type) {// 商品为流量或者套餐
                case 1: // 流量包
                    $ret = $this->activatePackage();
                    break;
                case 2: // 套餐
                    if (Order::userActivePlan(self::$user->id)->where('id', '<>', self::$order->id)->exists()) {// 判断套餐是否直接激活
                        $ret = $this->setPrepaidPlan();
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
        $ret = self::$user->updateCredit(self::$order->origin_amount);
        // 余额变动记录日志
        if ($ret) {
            Helpers::addUserCreditLog(self::$order->user_id, self::$order->id, $credit, self::$user->credit, self::$order->amount, '用户通过'.self::$order->pay_way.'充值余额');
        }

        return $ret;
    }

    private function activatePackage(): bool
    { // 激活流量包
        if (self::$user->incrementData(self::$goods->traffic * MB)) {
            return Helpers::addUserTrafficModifyLog(self::$order->user_id, self::$user->transfer_enable - self::$goods->traffic * MB, self::$user->transfer_enable, '['.self::$order->pay_way.']加上用户购买的套餐流量', self::$order->id);
        }

        return false;
    }

    private function setPrepaidPlan(): bool
    { // 设置预支付套餐, 刷新账号有效时间用于流量重置判断
        Order::whereId(self::$order->id)->first()->prepay(); // 直接编辑self::$order->prepay() [手动修改]会加不上

        return self::$user->update(['expired_at' => date('Y-m-d', strtotime(self::$user->expired_at.' +'.self::$goods->days.' days'))]);
    }

    public function activatePlan(): bool
    { // 激活套餐
        Order::whereId(self::$order->id)->first()->update(['expired_at' => date('Y-m-d H:i:s', strtotime(self::$goods->days.' days'))]);
        $oldData = self::$user->transfer_enable;
        $updateData = [
            'invite_num'  => self::$user->invite_num + (self::$goods->invite_num ?: 0),
            'level'       => self::$goods->level,
            'speed_limit' => self::$goods->speed_limit,
            'enable'      => 1,
        ];

        // 无端口用户 添加端口
        if (empty(self::$user->port)) {
            $updateData['port'] = Helpers::getPort();
        }

        if (self::$user->update(array_merge($this->resetTimeAndData(), $updateData))) {
            return Helpers::addUserTrafficModifyLog(self::$order->user_id, $oldData, self::$user->transfer_enable, '【'.self::$order->pay_way.'】加上用户购买的套餐流量', self::$order->id);
        }

        return false;
    }

    public function resetTimeAndData($expired_at = null): array
    { // 计算下次重置与账号过期时间
        $data = ['u' => 0, 'd' => 0];
        // 账号有效期
        if (! $expired_at) {
            $expired_at = date('Y-m-d', strtotime(self::$goods->days.' days'));
            foreach (Order::userPrepay(self::$order->user_id)->with('goods')->get() as $paidOrder) {//拿出可能存在的其余套餐, 推算最新的到期时间
                //取出对应套餐信息
                $expired_at = date('Y-m-d', strtotime("$expired_at +".$paidOrder->goods->days.' days'));
            }
            $data['expired_at'] = $expired_at;
        }

        //账号流量重置日期
        $nextResetTime = date('Y-m-d', strtotime(self::$goods->period.' days'));
        if ($nextResetTime >= $expired_at) {
            $nextResetTime = null;
        }

        return array_merge($data, [
            'transfer_enable' => self::$goods->traffic * MB,
            'reset_time'      => $nextResetTime,
        ]);
    }

    private function setCommissionExpense(User $user)
    { // 佣金计算
        $referralType = sysConfig('referral_type');

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
                return $inviter->commissionLogs()
                    ->create([
                        'invitee_id' => $user->id,
                        'order_id'   => self::$order->id,
                        'amount'     => self::$order->amount,
                        'commission' => self::$order->amount * sysConfig('referral_percent'),
                    ]);
            }
        }

        return true;
    }

    public function activatePrepaidPlan(): bool
    { // 激活预支付套餐
        self::$order->complete();

        return $this->activatePlan();
    }
}
