<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 工单
 * Class Ticket
 *
 * @package App\Http\Models
 * @property-read \App\Http\Models\User $User
 * @mixin \Eloquent
 */
class Ticket extends Model
{
    protected $table = 'ticket';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}