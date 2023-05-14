<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 用户登录日志.
 */
class UserLoginLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'user_login_log';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
