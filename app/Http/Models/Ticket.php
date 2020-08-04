<?php

namespace App\Http\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * 工单
 * Class Ticket
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Ticket extends Model
{
    protected $table = 'ticket';
    protected $primaryKey = 'id';

    function scopeUid($query)
    {
        return $query->where('user_id', Auth::user()->id);
    }

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}