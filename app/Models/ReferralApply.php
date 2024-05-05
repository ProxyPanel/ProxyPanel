<?php

namespace App\Models;

use App\Casts\money;
use App\Observers\ReferralApplyObserver;
use App\Utils\Helpers;
use Auth;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 返利申请.
 */
#[ObservedBy([ReferralApplyObserver::class])]
class ReferralApply extends Model
{
    protected $table = 'referral_apply';

    protected $casts = ['before' => money::class, 'after' => money::class, 'amount' => money::class, 'link_logs' => 'array'];

    protected $guarded = [];

    public function scopeUid(Builder $query): Builder
    {
        return $query->whereUserId(Auth::id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referral_logs(): Builder
    {
        return ReferralLog::whereIn('id', $this->link_logs);
    }

    protected function amountTag(): Attribute
    {
        return Attribute::make(get: fn () => Helpers::getPriceTag($this->amount));
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(get: fn () => match ($this->status) {
            1 => '<span class="badge badge-sm badge-info">'.trans('common.status.pending').'</span>',
            2 => trans('common.status.withdrawn'),
            default => '<span class="badge badge-sm badge-warning">'.trans('common.status.applying').'</span>',
        });
    }
}
