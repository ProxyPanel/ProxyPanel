<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 邀请码
 * Class Invite
 *
 * @package App\Http\Models
 * @property-read \App\Http\Models\User $Generator
 * @property-read \App\Http\Models\User $User
 * @mixin \Eloquent
 */
class Invite extends Model
{
    protected $table = 'invite';
    protected $primaryKey = 'id';

    public function Generator()
    {
        return $this->hasOne(User::class, 'id', 'uid');
    }

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'fuid');
    }

}