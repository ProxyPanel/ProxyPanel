<?php

namespace App\Services;

use App\Models\Goods;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReferralLog;
use App\Models\User;
use App\Utils\Helpers;
use Illuminate\Database\Eloquent\Collection;
use Log;

class OrderService
{
    private User $user;

    private ?Goods $goods;

    private ?Payment $payment;

    public function __construct(private readonly Order $order)
    { // 获取需要的信息
        $this->user = $order->user;
        $this->goods = $order->goods;
        $this->payment = $order->payment;
    }

    public function receivedPayment(): bool
    { // 支付成功后处理
        $payment = $this->payment;
        if ($payment && $payment->status !== 1) {// 是否为余额购买套餐
            $payment->complete();
        }

        $goods = $this->goods;
        if ($goods === null) {
            $ret = $this->chargeCredit();
        } else {
            switch ($goods->type) {// 商品为流量或者套餐
                case 1: // 流量包
                    $ret = $this->activatePackage();
                    break;
                case 2: // 套餐
                    if (Order::userActivePlan($this->user->id)->where('id', '<>', $this->order->id)->exists()) {// 判断套餐是否直接激活
                        $ret = $this->order->prepay();
                    } else {
                        $ret = $this->activatePlan();
                    }
                    $this->setCommissionExpense($this->user); // 返利
                    break;
                default:
                    Log::emergency('【处理订单】出现错误-未知套餐类型');
            }
        }

        return $ret ?? true;
    }

    private function chargeCredit(): bool
    { // 余额充值
        $credit = $this->user->credit;
        $ret = $this->user->updateCredit($this->order->origin_amount);
        // 余额变动记录日志
        if ($ret) {
            Helpers::addUserCreditLog($this->order->user_id, $this->order->id, $credit, $this->user->credit, $this->order->amount, 'The user topped up the balance.');
        }

        return $ret;
    }

    private function activatePackage(): bool
    { // 激活流量包
        if ($this->user->incrementData($this->goods->traffic * MiB)) {
            return Helpers::addUserTrafficModifyLog($this->order->user_id, $this->user->transfer_enable - $this->goods->traffic * MiB, $this->user->transfer_enable, trans("[:payment] plus the user's purchased data plan.", ['payment' => $this->order->pay_way]));
        }

        return false;
    }

    public function activatePlan(): bool
    { // 激活套餐
        $this->order->refresh()->updateQuietly(['expired_at' => date('Y-m-d H:i:s', strtotime($this->goods->days.' days')), 'status' => 2]);
        $oldData = $this->user->transfer_enable;
        // 套餐送的邀请名额走库里自增，不并进下面那次 update
        $inviteBonus = (int) ($this->goods->invite_num ?: 0);
        $updateData = [
            'level' => $this->goods->level,
            'speed_limit' => $this->goods->speed_limit,
            'enable' => 1,
            ...$this->resetTimeAndData(),
        ];

        // 无端口用户 添加端口
        if (empty($this->user->port)) {
            $updateData['port'] = Helpers::getPort();
        }

        if ($this->user->update($updateData)) {
            if ($inviteBonus > 0) {
                $this->user->increment('invite_num', $inviteBonus);
            }

            return Helpers::addUserTrafficModifyLog($this->order->user_id, $oldData, $this->user->transfer_enable, trans("[:payment] plus the user's purchased data plan.", ['payment' => $this->order->pay_way]), $this->order->id);
        }

        return false;
    }

    public function resetTimeAndData(?string $expired_at = null): array
    { // 计算下次重置与账号过期时间
        if (! $expired_at) { // 账号有效期
            $expired_at = $this->getFinallyExpiredTime();
        }

        return [
            'u' => 0,
            'd' => 0,
            'transfer_enable' => $this->goods->traffic * MiB,
            'expired_at' => $expired_at,
            'reset_time' => self::calculateResetTime($expired_at, $this->goods->period),
        ];
    }

    /**
     * 由「账号到期日」与「重置周期」推算下一次流量重置日期.
     *
     * 商品的 period 是「每 N 天自动重置流量」，留空（套餐允许）或 0 表示不自动重置。
     * 这一点很容易写错：曾经 period 为空时会被当成「0 天」算出今天的日期，而
     * TaskDaily::resetUserTraffic() 的判定是 reset_time <= 今天，于是这类用户的流量
     * 每天都会被清零（且重置后算出的还是今天，永远退出不了这个循环）。
     *
     * 这里只做日期推算，不碰数据库，便于单测（本项目的 Laravel 测试需要可达的数据库才能启动）。
     */
    public static function calculateResetTime(?string $expired_at, ?int $period): ?string
    {
        if (! $period) { // 无重置周期 / 周期为 0：不自动重置
            return null;
        }

        $nextResetTime = now()->addDays($period)->toDateString();

        // 重置日落在到期日当天或之后时无效：TaskDaily 只在 expired_at > 今天 时重置，用户永远等不到那次重置
        if (! $expired_at || $nextResetTime >= $expired_at) {
            return null;
        }

        return $nextResetTime;
    }

    private function getFinallyExpiredTime(): string
    { // 推算最新的到期时间
        $orders = $this->user->orders()->whereIn('status', [2, 3])->whereIsExpire(0)->isPlan()->get();

        return self::calculateExpiredAt($orders);
    }

    /**
     * 由「生效/预支付」的套餐订单推算账号最新到期时间.
     *
     * 起点是当前生效订单自己的到期日（没有则从今天），再加上其余订单的天数。
     * 这里只做集合运算，不碰数据库，便于单测（本项目的 Laravel 测试需要可达的数据库才能启动）。
     *
     * @param  Collection  $orders  状态为 2（已生效）或 3（预支付）的套餐订单，需预加载 goods
     */
    public static function calculateExpiredAt(Collection $orders): string
    {
        $current = $orders->firstWhere('status', 2);

        // 排除当前生效订单本身：它的到期日已含自己的天数，再累加就是重复计算
        $days = $orders->reject(static fn (Order $order): bool => $order->is($current))->sum('goods.days');

        // copy() 才不被 addDays() 改写模型的 expired_at 属性
        return ($current?->expired_at?->copy() ?? now())->addDays($days)->toDateString();
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

        if ($data['expired_at'] <= now()->toDateString()) {
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

        return $this->user->update($data);
    }
}
