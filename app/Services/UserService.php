<?php

namespace App\Services;

use App\Models\User;
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
}
