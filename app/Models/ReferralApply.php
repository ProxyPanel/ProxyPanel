<?php

namespace App\Models;

use App\Casts\money;
use App\Utils\Helpers;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 返利申请.
 */
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

    public function referral_logs(): ReferralLog|\Illuminate\Database\Query\Builder
    {
        return ReferralLog::whereIn('id', $this->link_logs);
    }

    public function getAmountTagAttribute(): string
    {
        return Helpers::getPriceTag($this->amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->attributes['status']) {
            1 => '<span class="badge badge-sm badge-info">'.trans('common.status.pending').'</span>',
            2 => trans('common.status.withdrawn'),
            default => '<span class="badge badge-sm badge-warning">'.trans('common.status.applying').'</span>',
        };
    }
}
