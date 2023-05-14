<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 工单回复.
 */
class TicketReply extends Model
{
    protected $table = 'ticket_reply';

    protected $guarded = [];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
