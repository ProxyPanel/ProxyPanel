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

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                1 => '<span class="badge badge-sm badge-info">'.trans('common.status.applying').'</span>',
                2 => '<span class="badge badge-sm badge-default">'.trans('common.status.withdrawn').'</span>',
                default => '<span class="badge badge-sm badge-success">'.trans('common.status.unwithdrawn').'</span>',
            },
        );
    }
}
