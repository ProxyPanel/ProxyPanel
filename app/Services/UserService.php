<?php

namespace App\Services;

use App\Models\User;
use Hashids\Hashids;

class UserService
{
    private static User $user;

    public function __construct(?User $user = null)
    {
        self::$user = $user ?? auth()->user();
    }

    public function getProfile(): array
    {
        $user = self::$user;

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
            'invite_url' => $this->inviteURI(),
        ];
    }

    public function inviteURI(bool $isCode = false): string
    {
        $uid = self::$user->id;
        $affSalt = sysConfig('aff_salt');
        $aff = empty($affSalt) ? $uid : (new Hashids($affSalt, 8))->encode($uid);

        return $isCode ? $aff : sysConfig('website_url').route('register', ['aff' => $aff], false);
    }

    public function isActivePaying(): bool
    {
        return self::$user->orders()->active()->where('origin_amount', '>', 0)->exists();
    }
}
