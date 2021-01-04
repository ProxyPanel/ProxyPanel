<?php

namespace App\Models;

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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function close() // 关闭支付单
    {
        return $this->update(['status' => -1]);
    }

    public function complete() // 完成支付单
    {
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

    // 订单状态
    public function getStatusLabelAttribute(): string
    {
        switch ($this->attributes['status']) {
            case -1:
                $status_label = '支付失败';
                break;
            case 1:
                $status_label = '支付成功';
                break;
            case 0:
            default:
                $status_label = '等待支付';
        }

        return $status_label;
    }
}
