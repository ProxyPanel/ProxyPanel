<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 账号余额操作日志.
 */
class UserCreditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'user_credit_log';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getBeforeAttribute($value)
    {
        return $value / 100;
    }

    public function setBeforeAttribute($value)
    {
        return $this->attributes['before'] = $value * 100;
    }

    public function getAfterAttribute($value)
    {
        return $value / 100;
    }

    public function setAfterAttribute($value)
    {
        return $this->attributes['after'] = $value * 100;
    }

    public function getAmountAttribute($value)
    {
        return $value / 100;
    }

    public function setAmountAttribute($value)
    {
        return $this->attributes['amount'] = $value * 100;
    }
}
