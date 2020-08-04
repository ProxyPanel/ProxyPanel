<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户登录日志
 * Class UserLoginLog
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class UserLoginLog extends Model
{
    protected $table = 'user_login_log';
    protected $primaryKey = 'id';

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}