<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 工单.
 */
class Ticket extends Model
{
    protected $table = 'ticket';

    public function scopeUid($query)
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
        switch ($this->attributes['status']) {
            case 0:
                $status_label = '<span class="badge badge-lg badge-success">'.trans('home.ticket_table_status_wait').'</span>';
                break;
            case 1:
                $status_label = '<span class="badge badge-lg badge-danger">'.trans('home.ticket_table_status_reply').'</span>';
                break;
            case 2:
                $status_label = '<span class="badge badge-lg badge-default">'.trans('home.ticket_table_status_close').'</span>';
                break;
            default:
                $status_label = '<span class="badge badge-lg badge-default"> 未知 </span>';
        }

        return $status_label;
    }
}
