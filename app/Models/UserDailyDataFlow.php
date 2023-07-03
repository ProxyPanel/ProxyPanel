<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 用户每日流量统计
 */
class UserDailyDataFlow extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'user_daily_data_flow';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    public function scopeUserDaily(Builder $query, int $uid): Builder
    { // 用户每天使用总流量
        return $query->whereUserId($uid)->whereNodeId(null);
    }
}
