<?php

namespace App\Http\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 邀请码
 * Class Invite
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Invite extends Model
{
    use SoftDeletes;

    protected $table = 'invite';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    function scopeUid($query)
    {
        return $query->where('uid', Auth::user()->id);
    }

    function generator()
    {
        return $this->hasOne(User::class, 'id', 'uid');
    }

    function user()
    {
        return $this->hasOne(User::class, 'id', 'fuid');
    }

}