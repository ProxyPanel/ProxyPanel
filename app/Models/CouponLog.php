<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 优惠券使用日志.
 */
class CouponLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'coupon_log';

    protected $guarded = [];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goods(): BelongsTo
    {
        return $this->belongsTo(Goods::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
