<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 工单回复
 * Class TicketReply
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class TicketReply extends Model
{
    protected $table = 'ticket_reply';
    protected $primaryKey = 'id';

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}