<?php

namespace App\Models;

use App\Utils\Helpers;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 支付单.
 */
class Payment extends Model
{
    protected $table = 'payment';

    protected $guarded = [];

    public function scopeUid($query)
    {
        return $query->whereUserId(Auth::id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function failed(): bool
    { // 关闭支付单
        return $this->close() && $this->order()->close();
    }

    public function close(): bool
    { // 关闭支付单
        return $this->update(['status' => -1]);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function complete(): bool
    { // 完成支付单
        return $this->update(['status' => 1]);
    }

    public function getAmountAttribute($value)
    {
        return $value / 100;
    }

    public function setAmountAttribute($value)
    {
        return $this->attributes['amount'] = $value * 100;
    }

    public function getAmountTagAttribute(): string
    {
        return Helpers::getPriceTag($this->amount);
    }

    // 订单状态
    public function getStatusLabelAttribute(): string
    {
        return match ($this->attributes['status']) {
            -1 => trans('common.failed_item', ['attribute' => trans('user.pay')]),
            1 => trans('common.success_item', ['attribute' => trans('user.pay')]),
            default => trans('common.payment.status.wait'),
        };
    }
}
