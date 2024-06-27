<?php

namespace App\Models;

use App\Components\Helpers;
use Hash;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * 用户信息.
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles, Sortable;

    public $sortable = ['id', 'credit', 'port', 't', 'expired_at'];
    protected $table = 'user';
    protected $casts = ['expired_at' => 'date:Y-m-d', 'reset_time' => 'date:Y-m-d', 'ban_time' => 'date:Y-m-d'];
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

    public function getExpirationDateAttribute()
    {
        return $this->attributes['expired_at'];
    }

    public function getResetDateAttribute()
    {
        return $this->attributes['reset_time'];
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

    public function setCreditAttribute($value)
    {
        return $this->attributes['credit'] = $value * 100;
    }

    public function getCreditTagAttribute(): string
    {
        return Helpers::getPriceTag($this->credit);
    }

    public function getTransferEnableFormattedAttribute()
    {
        return flowAutoShow($this->attributes['transfer_enable']);
    }

    public function getSpeedLimitAttribute()
    {
        return $this->attributes['speed_limit'] / Mbps;
    }

    public function setPasswordAttribute($password)
    {
        return $this->attributes['password'] = Hash::make($password);
    }

    public function setSpeedLimitAttribute($value)
    {
        return $this->attributes['speed_limit'] = $value * Mbps;
    }

    public function getAvatarAttribute(): string
    {
        if ($this->qq) {
            $url = "https://q1.qlogo.cn/g?b=qq&nk={$this->qq}&s=640";
        } elseif (stripos(strtolower($this->username), '@qq.com') !== false) {
            $url = "https://q1.qlogo.cn/g?b=qq&nk={$this->username}&s=640";
        } else {
            // $url = 'https://gravatar.loli.net/avatar/'.md5(strtolower(trim($this->username)))."?&d=identicon";
            // $url = 'https://robohash.org/'.md5(strtolower(trim($this->username))).'?set=set4&bgset=bg2&size=400x400';
            $url = 'https://api.btstu.cn/sjtx/api.php?lx=c1&format=images&method=zsy';
        }

        return $url;
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

    public function expiration_status(): int
    {
        $today = date('Y-m-d');
        $nextMonth = date('Y-m-d', strtotime('next month'));

        if ($this->expiration_date < $today) {
            $status = 0; // 已过期
        } elseif ($this->expiration_date === $today) {
            $status = 1; // 今日过期
        } elseif ($this->expiration_date <= $nextMonth) {
            $status = 2; // 一个月内过期
        }

        return $status ?? 3;
    }

    public function isTrafficWarning(): bool
    { // 流量异常警告
        return (sysConfig('traffic_ban_value') * GB) <= $this->recentTrafficUsed();
    }

    public function recentTrafficUsed()
    {
        return UserHourlyDataFlow::userRecentUsed($this->id)->sum('total');
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
