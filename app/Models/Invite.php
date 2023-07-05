<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 邀请码
 */
class Invite extends Model
{
    use SoftDeletes;

    protected $table = 'invite';

    protected $casts = ['dateline' => 'datetime', 'deleted_at' => 'datetime'];

    protected $guarded = [];

    public function scopeUid(Builder $query): Builder
    {
        return $query->whereInviterId(Auth::id());
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            0 => '<span class="badge badge-success">'.trans('common.status.unused').'</span>',
            1 => '<span class="badge badge-danger">'.trans('common.status.used').'</span>',
            2 => '<span class="badge badge-default">'.trans('common.status.expire').'</span>',
            default => '<span class="badge badge-default"> '.trans('common.status.unknown').' </span>',
        };
    }
}
