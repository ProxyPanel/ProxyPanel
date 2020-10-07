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

    // 1小时内流量异常用户
    public function trafficAbnormal(): array
    {
        $userTotalTrafficList = self::whereNodeId(0)
            ->where('total', '>', MB * 50)
            ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))
            ->groupBy('user_id')
            ->selectRaw("user_id, sum(total) as totalTraffic")->pluck('totalTraffic', 'user_id')
            ->toArray(); // 只统计50M以上的记录，加快速度
        foreach ($userTotalTrafficList as $user) {
            if ($user->totalTraffic > sysConfig('traffic_ban_value') * GB) {
                $result[] = $user->user_id;
            }
        }

        return $result ?? [];
    }
}
