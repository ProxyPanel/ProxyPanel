<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 节点配置信息.
 */
class Node extends Model
{
    protected $table = 'ss_node';
    protected $guarded = ['id', 'created_at'];

    public function labels(): HasMany
    {
        return $this->hasMany(NodeLabel::class);
    }

    public function heartBeats(): HasMany
    {
        return $this->hasMany(NodeHeartBeat::class);
    }

    public function onlineLogs(): HasMany
    {
        return $this->hasMany(NodeOnlineLog::class);
    }

    public function pingLogs(): HasMany
    {
        return $this->hasMany(NodePing::class);
    }

    public function dailyDataFlows(): HasMany
    {
        return $this->hasMany(NodeDailyDataFlow::class);
    }

    public function hourlyDataFlows(): HasMany
    {
        return $this->hasMany(NodeHourlyDataFlow::class);
    }

    public function rules(): hasMany
    {
        return $this->hasMany(NodeRule::class);
    }

    public function ruleGroup(): hasOne
    {
        return $this->hasOne(RuleGroupNode::class);
    }

    public function auth(): HasOne
    {
        return $this->hasOne(NodeAuth::class);
    }

    public function level_table(): HasOne
    {
        return $this->hasOne(Level::class, 'level', 'level');
    }

    public function scopeUserAllowNodes($query, $user_group_id, $user_level)
    {
        $userGroup = UserGroup::find($user_group_id);
        if ($userGroup) {
            $query->whereIn('id', $userGroup->nodes);
        }

        return $query->whereStatus(1)->where('level', '<=', $user_level ?: 0);
    }

    public function getSpeedLimitAttribute($value)
    {
        return $value / Mbps;
    }

    public function setSpeedLimitAttribute($value)
    {
        return $this->attributes['speed_limit'] = $value * Mbps;
    }

    public function getNodeAccessUsersAttribute()
    {
        return User::nodeAllowUsers($this->attributes['id'], $this->attributes['level'])->get();
    }

    public function getTypeLabelAttribute(): string
    {
        switch ($this->attributes['type']) {
            case 1:
                $type_label = 'ShadowsocksR';
                break;
            case 2:
                $type_label = 'V2Ray';
                break;
            case 3:
                $type_label = 'Trojan';
                break;
            case 4:
                $type_label = 'VNet';
                break;
            default:
                $type_label = 'UnKnown';
        }

        return $type_label;
    }
}
