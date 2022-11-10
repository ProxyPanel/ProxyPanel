<?php

namespace App\Models;

use Hash;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;
use Spatie\Permission\Traits\HasRoles;

/**
 * 用户信息.
 */
class User extends Authenticatable
{
    use Notifiable, HasRoles, Sortable;

    public $sortable = ['id', 'credit', 'port', 't', 'expired_at'];
    protected $table = 'user';
    protected $casts = ['expired_at' => 'date:Y-m-d', 'reset_time' => 'date:Y-m-d', 'ban_time' => 'date:Y-m-d'];
    protected $dates = ['expired_at', 'reset_time'];
    protected $guarded = [];

    public function routeNotificationForMail($notification)
    {
        return $this->username;
    }

    public function usedTrafficPercentage()
    {
        return round(($this->used_traffic) / $this->transfer_enable, 2);
    }

    public function getUsedTrafficAttribute(): int
    {
        return $this->d + $this->u;
    }

    public function getTelegramUserIdAttribute()
    {
        $telegram = $this->userAuths()->whereType('telegram')->first();

        return $telegram->identifier ?? null;
    }

    public function userAuths(): HasMany
    {
        return $this->hasMany(UserOauth::class);
    }

    public function profile()
    {
        return [
            'id'              => $this->id,
            'nickname'        => $this->nickname,
            'account'         => $this->username,
            'port'            => $this->port,
            'passwd'          => $this->passwd,
            'uuid'            => $this->vmess_id,
            'transfer_enable' => $this->transfer_enable,
            'u'               => $this->u,
            'd'               => $this->d,
            't'               => $this->t,
            'enable'          => $this->enable,
            'speed_limit'     => $this->speed_limit,
            'credit'          => $this->credit,
            'expired_at'      => $this->expired_at,
            'ban_time'        => $this->ban_time,
            'level'           => $this->level_name,
            'group'           => $this->userGroup->name ?? null,
            'last_login'      => $this->last_login,
            'reset_time'      => $this->reset_time,
            'invite_num'      => $this->invite_num,
            'status'          => $this->status,
        ];
    }

    public function onlineIpLogs(): HasMany
    {
        return $this->hasMany(NodeOnlineIp::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function commissionSettlements(): HasMany
    {
        return $this->hasMany(ReferralApply::class);
    }

    public function commissionLogs(): HasMany
    {
        return $this->hasMany(ReferralLog::class, 'inviter_id');
    }

    public function ruleLogs(): HasMany
    {
        return $this->hasMany(RuleLog::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function banedLogs(): HasMany
    {
        return $this->hasMany(UserBanedLog::class);
    }

    public function creditLogs(): HasMany
    {
        return $this->hasMany(UserCreditLog::class);
    }

    public function dailyDataFlows(): HasMany
    {
        return $this->hasMany(UserDailyDataFlow::class);
    }

    public function dataFlowLogs(): HasMany
    {
        return $this->hasMany(UserDataFlowLog::class);
    }

    public function dataModifyLogs(): HasMany
    {
        return $this->hasMany(UserDataModifyLog::class);
    }

    public function hourlyDataFlows(): HasMany
    {
        return $this->HasMany(UserHourlyDataFlow::class);
    }

    public function loginLogs(): HasMany
    {
        return $this->HasMany(UserLoginLog::class);
    }

    public function subscribe(): HasOne
    {
        return $this->hasOne(UserSubscribe::class);
    }

    public function subUrl()
    {
        return route('sub', $this->subscribe->code);
    }

    public function subscribeLogs(): HasManyThrough
    {
        return $this->hasManyThrough(UserSubscribeLog::class, UserSubscribe::class);
    }

    public function verify(): HasMany
    {
        return $this->hasMany(Verify::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(__CLASS__);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class, 'inviter_id');
    }

    public function invitees(): HasMany
    {
        return $this->hasMany(__CLASS__, 'inviter_id');
    }

    public function getLevelNameAttribute(): string
    {
        return Level::whereLevel($this->attributes['level'])->first()->name;
    }

    public function getCreditAttribute()
    {
        return $this->attributes['credit'] / 100;
    }

    public function getTransferEnableFormattedAttribute()
    {
        return flowAutoShow($this->attributes['transfer_enable']);
    }

    public function getSpeedLimitAttribute()
    {
        return $this->attributes['speed_limit'] / Mbps;
    }

    public function getExpiredAtAttribute()
    {
        return $this->attributes['expired_at'];
    }

    public function getResetTimeAttribute()
    {
        return $this->attributes['reset_time'];
    }

    public function setPasswordAttribute($password)
    {
        return $this->attributes['password'] = Hash::make($password);
    }

    public function setCreditAttribute($value)
    {
        return $this->attributes['credit'] = $value * 100;
    }

    public function setSpeedLimitAttribute($value)
    {
        return $this->attributes['speed_limit'] = $value * Mbps;
    }

    public function scopeActiveUser($query)
    {
        return $query->where('status', '<>', -1)->whereEnable(1);
    }

    public function scopeBannedUser($query)
    {
        return $query->where('status', '>=', 0)->whereEnable(0);
    }

    public function nodes($userLevel = -1, $userGroupId = -1)
    {
        if ($userGroupId === -1 && $this->attributes['user_group_id']) {
            $query = $this->userGroup->nodes();
        } elseif ($userGroupId !== -1 && $userGroupId) {
            $query = UserGroup::findOrFail($userGroupId)->nodes();
        } else {
            $query = Node::query();
        }

        return $query->whereStatus(1)->where('level', '<=', $userLevel !== -1 && $userLevel !== null ? $userLevel : $this->attributes['level'] ?? 0);
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    public function getIsAvailableAttribute(): bool
    {
        return ! $this->ban_time && $this->transfer_enable && $this->expired_at > time();
    }

    public function updateCredit(float $credit): bool
    {
        $this->credit += $credit;

        return $this->credit >= 0 && $this->save();
    }

    public function incrementData(int $data): bool
    { // 添加用户流量
        $this->transfer_enable += $data;

        return $this->save();
    }

    public function isNotCompleteOrderByUserId(int $userId): bool
    { // 添加用户余额
        return Order::uid($userId)->whereIn('status', [0, 1])->exists();
    }

    public function trafficFetch(int $u, int $d): bool
    {
        $this->u += $u;
        $this->d += $d;

        return $this->save();
    }

    public function expired_status(): int
    {
        $expired_status = 2; // 大于一个月过期
        if ($this->expired_at < date('Y-m-d')) {
            $expired_status = -1; // 已过期
        } elseif ($this->expired_at === date('Y-m-d')) {
            $expired_status = 0; // 今天过期
        } elseif ($this->expired_at > date('Y-m-d') && $this->expired_at <= date('Y-m-d', strtotime('30 days'))) {
            $expired_status = 1; // 最近一个月过期
        }

        return $expired_status;
    }

    public function isTrafficWarning(): bool
    { // 流量异常警告
        return (sysConfig('traffic_ban_value') * GB) <= $this->recentTrafficUsed();
    }

    public function recentTrafficUsed()
    {
        return UserHourlyDataFlow::userRecentUsed($this->id)->sum('total');
    }

    public function activePayingUser()
    { //付费用户判断
        return $this->orders()->active()->where('origin_amount', '>', 0)->exists();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function routeNotificationForTelegram()
    {
        return $this->telegram_user_id;
    }
}
