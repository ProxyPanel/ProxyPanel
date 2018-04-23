<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 工单回复
 * Class TicketReply
 *
 * @package App\Http\Models
 */
class TicketReply extends Model
{
    protected $table = 'ticket_reply';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}