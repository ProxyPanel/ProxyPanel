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

    /**
     * 邀请码状态的语义值：徽标样式交给 x-badge。
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            0 => ['type' => 'success', 'text' => trans('common.status.unused')],
            1 => ['type' => 'danger', 'text' => trans('common.status.used')],
            2 => ['type' => 'default', 'text' => trans('common.status.expire')],
            default => ['type' => 'default', 'text' => trans('common.status.unknown')],
        };
    }
}
