<?php

namespace App\Models;

use Hash;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * 用户信息
 */
class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';
    protected $casts = ['expired_at' => 'date:Y-m-d', 'reset_time' => 'date:Y-m-d'];
    protected $dates = ['expired_at', 'reset_time'];
    protected $guarded = ['id'];

    public function onlineIpLogs(): HasMany
    {
        return $this->hasMany(NodeOnlineUserIp::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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

    public function subscribeLogs(): HasManyThrough
    {
        return $this->hasManyThrough(UserSubscribeLog::class, UserSubscribe::class);
    }

    public function verify(): HasMany
    {
        return $this->hasMany(Verify::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
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

    public function scopeNodeAllowUsers($query, $node_id, $node_level)
    {
        $groups = [0];
        if ($node_id) {
            foreach (UserGroup::all() as $userGroup) {
                $nodes = $userGroup->nodes;
                if ($nodes && in_array($node_id, $nodes, true)) {
                    $groups[] = $userGroup->id;
                }
            }
        }

        return $query->activeUser()->whereIn('group_id', $groups)->where('level', '>=', $node_level);
    }

    public function scopeUserAccessNodes()
    {
        return Node::userAllowNodes($this->attributes['group_id'], $this->attributes['level']);
    }
}
