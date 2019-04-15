<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 账号余额操作日志
 * Class UserBalanceLog
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class UserBalanceLog extends Model
{
    protected $table = 'user_balance_log';
    protected $primaryKey = 'id';
    public $timestamps = false;

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    function getBeforeAttribute($value)
    {
        return $value / 100;
    }

    function setBeforeAttribute($value)
    {
        return $this->attributes['before'] = $value * 100;
    }

    function getAfterAttribute($value)
    {
        return $value / 100;
    }

    function setAfterAttribute($value)
    {
        return $this->attributes['after'] = $value * 100;
    }

    function getAmountAttribute($value)
    {
        return $value / 100;
    }

    function setAmountAttribute($value)
    {
        return $this->attributes['amount'] = $value * 100;
    }
}