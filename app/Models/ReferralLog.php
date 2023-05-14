<?php

namespace App\Models;

use App\Components\Helpers;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 返利日志.
 */
class ReferralLog extends Model
{
    protected $table = 'referral_log';

    protected $guarded = [];

    public function scopeUid($query)
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

    public function getAmountAttribute($value)
    {
        return $value / 100;
    }

    public function getCommissionAttribute($value)
    {
        return $value / 100;
    }

    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = $value * 100;
    }

    public function setCommissionAttribute($value): void
    {
        $this->attributes['commission'] = $value * 100;
    }

    public function getAmountTagAttribute(): string
    {
        return Helpers::getPriceTag($this->amount);
    }

    public function getCommissionTagAttribute(): string
    {
        return Helpers::getPriceTag($this->commission);
    }

    public function getStatusLabelAttribute(): string
    {
        switch ($this->attributes['status']) {
            case 1:
                $status_label = '<span class="badge badge-sm badge-info">'.trans('common.status.applying').'</span>';
                break;
            case 2:
                $status_label = '<span class="badge badge-sm badge-default">'.trans('common.status.withdrawn').'</span>';
                break;
            default:
                $status_label = '<span class="badge badge-sm badge-success">'.trans('common.status.unwithdrawn').'</span>';
        }

        return $status_label;
    }
}
