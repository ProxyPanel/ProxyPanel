<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 用户流量变动记录.
 */
class UserDataModifyLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'user_data_modify_log';

    protected $guarded = [];

    // 关联账号
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 关联订单
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getBeforeAttribute($value): string
    {
        return $this->attributes['before'] = formatBytes($value);
    }

    public function getAfterAttribute($value): string
    {
        return $this->attributes['after'] = formatBytes($value);
    }
}
