<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户标签
 * Class UserLabel
 *
 * @package App\Http\Models
 * @property-read \App\Http\Models\User $user
 * @mixin \Eloquent
 */
class UserLabel extends Model
{
    protected $table = 'user_label';
    protected $primaryKey = 'id';
    public $timestamps = false;

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}