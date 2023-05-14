<?php

namespace App\Models;

use App\Components\Helpers;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 返利申请.
 */
class ReferralApply extends Model
{
    protected $table = 'referral_apply';

    protected $casts = ['link_logs' => 'array'];

    protected $guarded = [];

    public function scopeUid($query)
    {
        return $query->whereUserId(Auth::id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referral_logs()
    {
        return ReferralLog::whereIn('id', $this->link_logs);
    }

    public function getBeforeAttribute($value)
    {
        return $value / 100;
    }

    public function setBeforeAttribute($value): void
    {
        $this->attributes['before'] = $value * 100;
    }

    public function getAfterAttribute($value)
    {
        return $value / 100;
    }

    public function setAfterAttribute($value): void
    {
        $this->attributes['after'] = $value * 100;
    }

    public function getAmountAttribute($value)
    {
        return $value / 100;
    }

    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = $value * 100;
    }

    public function getAmountTagAttribute(): string
    {
        return Helpers::getPriceTag($this->amount);
    }

    public function getStatusLabelAttribute(): string
    {
        switch ($this->attributes['status']) {
            case 1:
                $status_label = '<span class="badge badge-sm badge-info">'.trans('common.status.pending').'</span>';
                break;
            case 2:
                $status_label = trans('common.status.withdrawn');
                break;
            case 0:
            default:
                $status_label = '<span class="badge badge-sm badge-warning">'.trans('common.status.applying').'</span>';
        }

        return $status_label;
    }
}
