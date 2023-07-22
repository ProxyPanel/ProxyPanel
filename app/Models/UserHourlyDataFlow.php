<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 用户流量每小时统计
 */
class UserHourlyDataFlow extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'user_hourly_data_flow';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    // 用户每时使用总流量
    public function scopeUserHourly(Builder $query, int $uid): Builder
    {
        return $query->whereUserId($uid)->whereNodeId(null);
    }

    public function scopeUserRecentUsed(Builder $query, int $uid): Builder
    {
        return $query->userHourly($uid)->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900));
    }

    // 1小时内流量异常用户
    public function trafficAbnormal(): array
    {
        $userTotalTrafficList = self::whereNodeId(null)
            ->where('d', '>', MiB * 10)
            ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))
            ->groupBy('user_id')
            ->selectRaw('user_id, sum(u + d) as total')->pluck('total', 'user_id')
            ->toArray(); // 只统计10M以上的记录，加快速度
        foreach ($userTotalTrafficList as $user => $traffic) {
            if ($traffic > (int) sysConfig('traffic_ban_value') * GiB) {
                $result[] = $user;
            }
        }

        return $result ?? [];
    }
}
