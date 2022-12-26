<?php

namespace App\Services;

use Hashids\Hashids;

class UserService extends BaseService
{
    private static $user;

    public function __construct($user = null)
    {
        parent::__construct();
        if (isset($user)) {
            self::$user = $user;
        } else {
            self::$user = auth()->user();
        }
    }

    public function getProfile()
    {
        $user = self::$user;

        return [
            'id'              => $user->id,
            'nickname'        => $user->nickname,
            'account'         => $user->username,
            'port'            => $user->port,
            'passwd'          => $user->passwd,
            'uuid'            => $user->vmess_id,
            'transfer_enable' => $user->transfer_enable,
            'u'               => $user->u,
            'd'               => $user->d,
            't'               => $user->t,
            'enable'          => $user->enable,
            'speed_limit'     => $user->speed_limit,
            'credit'          => $user->credit,
            'expired_at'      => strtotime($user->expired_at),
            'ban_time'        => $user->ban_time,
            'level'           => $user->level_name,
            'group'           => $user->userGroup->name ?? null,
            'last_login'      => $user->last_login,
            'reset_time'      => $user->reset_date,
            'invite_num'      => $user->invite_num,
            'status'          => $user->status,
            'invite_url'      => $this->inviteURI(),
        ];
    }

    public function inviteURI($isCode = false): string
    {
        $affSalt = sysConfig('aff_salt');
        if (isset($affSalt)) {
            $aff = (new Hashids($affSalt, 8))->encode(self::$user->id());
        } else {
            $aff = self::$user->id;
        }

        return $isCode ? $aff : sysConfig('website_url').route('register', ['aff' => 1], false);
    }

    public function isActivePaying(): bool
    {
        return self::$user->orders()->active()->where('origin_amount', '>', 0)->exists();
    }
}
