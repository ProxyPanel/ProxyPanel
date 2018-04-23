<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 支付单
 * Class Payment
 *
 * @package App\Http\Models
 */
class Payment extends Model
{
    protected $table = 'payment';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'oid', 'oid');
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
