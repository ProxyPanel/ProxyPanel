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
 * 返利日志.
 */
class ReferralLog extends Model
{
    protected $table = 'referral_log';

    protected $casts = ['amount' => money::class, 'commission' => money::class];

    protected $guarded = [];

    public function scopeUid(Builder $query): Builder
    {
        return $query->whereInviterId(Auth::id());
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function amountTag(): Attribute
    {
        return Attribute::make(
            get: fn () => Helpers::getPriceTag($this->amount),
        );
    }

    protected function commissionTag(): Attribute
    {
        return Attribute::make(
            get: fn () => Helpers::getPriceTag($this->commission),
        );
    }

    /**
     * 返利记录的语义状态：徽标样式交给 x-badge。
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                1 => ['type' => 'info', 'size' => 'sm', 'text' => trans('common.status.applying')],
                2 => ['type' => 'default', 'size' => 'sm', 'text' => trans('common.status.withdrawn')],
                default => ['type' => 'success', 'size' => 'sm', 'text' => trans('common.status.withdrawal_pending')],
            },
        );
    }
}
