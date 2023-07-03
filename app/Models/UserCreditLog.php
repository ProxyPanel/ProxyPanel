<?php

namespace App\Models;

use App\Casts\money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 账号余额操作日志.
 */
class UserCreditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'user_credit_log';

    protected $casts = ['before' => money::class, 'after' => money::class, 'amount' => money::class];

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
