<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 返利日志.
 */
class ReferralLog extends Model
{
    protected $table = 'referral_log';

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

    public function getStatusLabelAttribute(): string
    {
        switch ($this->attributes['status']) {
            case 1:
                $status_label = '<span class="badge badge-sm badge-info">'.trans('user.status.applying').'</span>';
                break;
            case 2:
                $status_label = '<span class="badge badge-sm badge-default">'.trans('user.status.withdrawn').'</span>';
                break;
            default:
                $status_label = '<span class="badge badge-sm badge-success">'.trans('user.status.not_withdrawn').'</span>';
        }

        return $status_label;
    }
}
