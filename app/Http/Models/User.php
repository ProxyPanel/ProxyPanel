<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户信息
 * Class User
 *
 * @package App\Http\Models
 */
class User extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';

    function payment()
    {
        return $this->hasMany(Payment::class, 'user_id', 'id');
    }

    function label()
    {
        return $this->hasMany(UserLabel::class, 'user_id', 'id');
    }

    function getBalanceAttribute($value)
    {
        return $value / 100;
    }

    function setBalanceAttribute($value)
    {
        return $this->attributes['balance'] = $value * 100;
    }
}