<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 返利日志
 * Class ReferralLog
 *
 * @package App\Http\Models
 */
class ReferralLog extends Model
{
    protected $table = 'referral_log';
    protected $primaryKey = 'id';

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    function ref_user()
    {
        return $this->hasOne(User::class, 'id', 'ref_user_id');
    }

    function order()
    {
        return $this->hasOne(Order::class, 'oid', 'order_id');
    }

    function getAmountAttribute($value)
    {
        return $value / 100;
    }

    function setAmountAttribute($value)
    {
        $this->attributes['amount'] = $value * 100;
    }

    function getRefAmountAttribute($value)
    {
        return $value / 100;
    }

    function setRefAmountAttribute($value)
    {
        $this->attributes['ref_amount'] = $value * 100;
    }
}