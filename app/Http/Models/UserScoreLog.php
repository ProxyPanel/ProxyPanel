<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 账号积分操作日志
 * Class UserScoreLog
 *
 * @package App\Http\Models
 */
class UserScoreLog extends Model
{
    protected $table = 'user_score_log';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}