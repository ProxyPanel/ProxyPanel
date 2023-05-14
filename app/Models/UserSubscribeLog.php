<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * 用户订阅地址请求日志.
 */
class UserSubscribeLog extends Model
{
    public const CREATED_AT = 'request_time';

    public const UPDATED_AT = null;

    protected $table = 'user_subscribe_log';

    protected $guarded = [];

    public function subscribe(): BelongsTo
    {
        return $this->belongsTo(UserSubscribe::class, 'user_subscribe_id');
    }

    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, UserSubscribe::class, 'id', 'id', 'user_subscribe_id', 'user_id');
    }
}
