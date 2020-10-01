<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 用户流量每小时统计
 */
class UserHourlyDataFlow extends Model
{
    public const UPDATED_AT = null;
    protected $table = 'user_hourly_data_flow';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    // 用户每时使用总流量
    public function scopeUserHourly($query, $uid)
    {
        return $query->whereUserId($uid)->whereNodeId(0);
    }

    public function scopeUserRecentUsed($query, $uid)
    {
        return $query->userHourly($uid)->where('created_at', '>=', date('Y-m-d H:55'));
    }
}
