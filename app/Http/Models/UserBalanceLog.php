<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 账号余额操作日志
 * Class UserBalanceLog
 * @package App\Http\Models
 */
class UserBalanceLog extends Model
{
    protected $table = 'user_balance_log';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}