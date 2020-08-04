<?php

namespace App\Http\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户订阅地址
 * Class UserSubscribe
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class UserSubscribe extends Model
{
    protected $table = 'user_subscribe';
    protected $primaryKey = 'id';

    function scopeUid($query)
    {
        return $query->where('user_id', Auth::user()->id);
    }

    function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}