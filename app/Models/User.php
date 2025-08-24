<?php

namespace App\Models;

use App\Casts\data_rate;
use App\Casts\money;
use App\Observers\UserObserver;
use App\Utils\Avatar;
use App\Utils\Helpers;
use Hash;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, Sortable;

    public array $sortable = ['id', 'credit', 'port', 't', 'expired_at'];

    protected $table = 'user';

    protected $casts = ['credit' => money::class, 'speed_limit' => data_rate::class, 'expired_at' => 'date:Y-m-d', 'reset_time' => 'date:Y-m-d', 'ban_time' => 'date:Y-m-d'];

    protected $guarded = [];

    public function routeNotificationForMail($notification): string
    {
        return $this->username;
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

    public function latestLoginLog(): HasOne
    {
        return $this->hasOne(UserLoginLog::class)->latestOfMany();
    }

    public function subscribe(): HasOne
    {
        return $this->hasOne(UserSubscribe::class);
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

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paidOrders(): hasMany
    {
        return $this->hasMany(Order::class)->where('status', 2)->whereNotNull('goods_id')->where('is_expire', 0)->where('amount', '>', 0);
    }

    public function scopeActiveUser(Builder $query): Builder
    {
        return $query->where('status', '<>', -1)->whereEnable(1);
    }

    public function scopeBannedUser(Builder $query): Builder
    {
        return $query->where('status', '<>', -1)->whereEnable(0);
    }

    public function getExpirationDateAttribute(): ?string
    {
        return $this->attributes['expired_at'];
    }

    public function getResetDateAttribute(): ?string
    {
        return $this->attributes['reset_time'];
    }

    public function getTelegramUserIdAttribute(): ?string
    {
        $telegram = $this->userAuths()->whereType('telegram')->first();

        return $telegram->identifier ?? null;
    }

    public function userAuths(): HasMany
    {
        return $this->hasMany(UserOauth::class);
    }

    public function getCreditTagAttribute(): string
    {
        return Helpers::getPriceTag($this->credit);
    }

    public function getTransferEnableFormattedAttribute(): string
    {
        return formatBytes($this->attributes['transfer_enable']);
    }

    public function getExpirationStatusAttribute(): int
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

    public function getSubUrlAttribute(): string
    {
        return route('sub', $this->subscribe->code);
    }

    public function getInviteCodeAttribute(): string
    {
        $uid = $this->id;
        $affSalt = sysConfig('affiliate_link_salt');

        return empty($affSalt) ? $uid : (new Hashids($affSalt, 8))->encode($uid);
    }

    public function getInviteUrlAttribute(): string
    {
        return sysConfig('website_url').route('register', ['aff' => $this->invite_code], false);
    }

    public function getUsedTrafficAttribute(): int
    {
        return $this->d + $this->u;
    }

    public function getUnusedTrafficAttribute(): int
    {
        return max($this->transfer_enable - $this->d - $this->u, 0);
    }

    public function getAvatarAttribute(): string
    {
        return session()->remember('avatar_url_'.$this->id, function () {
            if ($this->qq) {
                return Avatar::getQQAvatar($this->qq);
            }

            if (str(str($this->username)->lower())->endsWith('@qq.com')) {
                return Avatar::getQQAvatar($this->username);
            }

            return Avatar::getRandomAvatar($this->username);
        });
    }

    public function getLevelNameAttribute(): string
    {
        return Level::where('level', $this->level)->value('name');
    }

    public function setPasswordAttribute(string $password): string
    {
        return $this->attributes['password'] = Hash::make($password);
    }

    public function nodes(?int $userLevel = null, ?int $userGroupId = null): Builder
    {
        if ($userGroupId === null && $this->user_group_id) { // 使用默认的用户分组
            $query = $this->userGroup->nodes()->getQuery();
        } elseif ($userGroupId) { // 使用给的用户分组
            $query = UserGroup::findOrFail($userGroupId)->nodes()->getQuery();
        } else { // 无用户分组
            $query = Node::query();
        }

        return $query->whereStatus(1)->where('level', '<=', $userLevel ?? $this->level ?? 0);
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

    public function routeNotificationForTelegram()
    {
        return $this->telegram_user_id;
    }
}
