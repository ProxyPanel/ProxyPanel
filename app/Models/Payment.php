<?php

namespace App\Models;

use App\Casts\money;
use App\Utils\Helpers;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 支付单.
 */
class Payment extends Model
{
    protected $table = 'payment';

    protected $guarded = [];

    protected $casts = ['amount' => money::class];

    public function scopeUid(Builder $query): Builder
    {
        return $query->whereUserId(Auth::id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function failed(): bool
    { // 关闭支付单
        return $this->close() && $this->order->close();
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

    protected function amountTag(): Attribute
    {
        return Attribute::make(
            get: fn () => Helpers::getPriceTag($this->amount),
        );
    }

    protected function statusLabel(): Attribute
    { // 订单状态
        return Attribute::make(
            get: fn () => match ($this->status) {
                -1 => trans('common.failed_item', ['attribute' => trans('user.pay')]),
                1 => trans('common.success_item', ['attribute' => trans('user.pay')]),
                default => trans('common.payment.status.wait'),
            },
        );
    }
}
