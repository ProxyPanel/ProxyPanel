<?php

namespace App\Models;

use App\Observers\TicketObserver;
use Auth;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 工单.
 */
#[ObservedBy([TicketObserver::class])]
class Ticket extends Model
{
    protected $table = 'ticket';

    protected $guarded = [];

    public function scopeUid(Builder $query): Builder
    {
        return $query->whereUserId(Auth::id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reply(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function close(): bool
    {
        $this->status = 2;

        return $this->save();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            0 => '<span class="badge badge-lg badge-success">'.trans('common.status.pending').'</span>',
            1 => '<span class="badge badge-lg badge-danger">'.trans('common.status.reply').'</span>',
            2 => '<span class="badge badge-lg badge-default">'.trans('common.status.closed').'</span>',
            default => '<span class="badge badge-lg badge-default">'.trans('common.status.unknown').'</span>',
        };
    }
}
