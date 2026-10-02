<?php

namespace App\Models;

use App\Casts\money;
use App\Utils\Helpers;
use App\Utils\Payments\PaymentManager;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 支付回调日志.
 */
class PaymentCallback extends Model
{
    protected $table = 'payment_callback';

    protected $guarded = [];

    public const STATUS_UNFULFILLED = 0;

    public const STATUS_FULFILLED = 1;

    protected $casts = ['amount' => money::class];

    protected function amountTag(): Attribute
    {
        return Attribute::make(
            get: fn () => Helpers::getPriceTag($this->amount),
        );
    }

    protected function statusLabel(): Attribute
    { // 0 涵盖验签失败与尚未履约，不等于失败
        return Attribute::make(
            get: fn () => (int) $this->status === self::STATUS_FULFILLED
                ? trans('common.success_item', ['attribute' => trans('user.pay')])
                : trans('common.status.pending'),
        );
    }

    protected function methodLabel(): Attribute
    { // 取自网关 metadata 的标签表，认不出时退回原始 key
        return Attribute::make(
            get: fn () => PaymentManager::getLabels(true)[$this->method] ?? $this->method,
        );
    }

    /**
     * 关联的本地支付单：trade_no 对不上时为 null。
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'trade_no', 'trade_no');
    }
}
