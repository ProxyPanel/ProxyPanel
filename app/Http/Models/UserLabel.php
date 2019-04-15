<?php

namespace App\Http\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户标签
 * Class UserLabel
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class UserLabel extends Model
{
    protected $table = 'user_label';
    protected $primaryKey = 'id';
    public $timestamps = false;

    function scopeUid($query)
    {
        return $query->where('user_id', Auth::user()->id);
    }

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}