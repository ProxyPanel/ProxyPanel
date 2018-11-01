<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户封禁日志
 * Class UserBanLog
 *
 * @package App\Http\Models
 * @property-read \App\Http\Models\User $User
 * @mixin \Eloquent
 */
class UserBanLog extends Model
{
    protected $table = 'user_ban_log';
    protected $primaryKey = 'id';

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}