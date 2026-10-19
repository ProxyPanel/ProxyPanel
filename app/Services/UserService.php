<?php

namespace App\Services;

use App\Models\User;
use App\Utils\Helpers;
use DB;
use RuntimeException;

class UserService
{
    private ?User $user;

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    public function getProfile(): array
    {
        $user = $this->getUser();

        return [
            'id' => $user->id,
            'nickname' => $user->nickname,
            'account' => $user->username,
            'port' => $user->port,
            'passwd' => $user->passwd,
            'uuid' => $user->vmess_id,
            'transfer_enable' => $user->transfer_enable,
            'u' => $user->u,
            'd' => $user->d,
            't' => $user->t,
            'enable' => $user->enable,
            'speed_limit' => $user->speed_limit,
            'credit' => $user->credit,
            'expired_at' => strtotime($user->expired_at),
            'ban_time' => $user->ban_time,
            'level' => $user->level_name,
            'group' => $user->userGroup->name ?? null,
            'last_login' => $user->last_login,
            'reset_time' => $user->reset_date,
            'invite_num' => $user->invite_num,
            'status' => $user->status,
            'invite_url' => $user->invite_url,
        ];
    }

    private function getUser(): User
    {
        if (! $this->user || ! $this->user->exists) {
            $user = auth()->user();
            if (! $user) {
                throw new RuntimeException('User not authenticated');
            }
            $this->setUser($user);
        }

        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getRemainingDays(): int
    {
        return now()->diffInDays($this->getUser()->expired_at, false);
    }

    public function getResetDays(): ?int
    {
        return $this->getUser()->reset_time ? now()->diffInDays($this->getUser()->reset_time, false) : null;
    }

    public function getUnusedTrafficPercent(): float
    {
        $totalTransfer = $this->getUser()->transfer_enable;

        return $totalTransfer > 0 ? round($this->getUser()->unused_traffic * 100 / $totalTransfer, 2) : 0;
    }

    public function isTrafficWarning(): bool
    { // 流量异常警告
        return ((int) sysConfig('traffic_abuse_limit') * GiB) <= $this->recentTrafficUsed();
    }

    public function recentTrafficUsed(): int
    {
        return $this->getUser()->hourlyDataFlows()->userRecentUsed()
            ->sum(DB::raw('u + d')); // 假设 traffic_used 是记录流量消耗的字段
    }

    public function isActivePaying(): bool
    {
        return $this->getUser()->orders()->active()->where('origin_amount', '>', 0)->exists();
    }

    /**
     * 签到发流量：时间窗内只有 cache()->add 认领成功的那一次会发.
     *
     * 只回状态与文案，响应封装留给调用方（网页端与客户端的形状不同）。
     * state：disabled-未开启签到、repeated-本周期已签过、failed-发流量失败、success-已发放。
     */
    public function checkIn(): array
    {
        $user = $this->getUser();

        if (! sysConfig('checkin_interval')) {
            return ['state' => 'disabled', 'message' => trans('user.home.attendance.disable')];
        }

        if (! cache()->add('userCheckIn_'.$user->id, '1', sysConfig('checkin_interval') * Minute)) {
            return ['state' => 'repeated', 'message' => trans('user.home.attendance.done')];
        }

        $traffic = random_int((int) sysConfig('checkin_reward'), (int) sysConfig('checkin_reward_max')) * MiB;
        $before = $user->transfer_enable;

        if (! $user->incrementData($traffic)) {
            cache()->forget('userCheckIn_'.$user->id); // 发失败要让人能重签

            return ['state' => 'failed', 'message' => trans('user.home.attendance.failed')];
        }

        Helpers::addUserTrafficModifyLog($user->id, $before, $user->transfer_enable, trans('user.home.attendance.attribute'));

        return ['state' => 'success', 'message' => trans('user.home.attendance.success', ['data' => formatBytes($traffic)])];
    }
}
