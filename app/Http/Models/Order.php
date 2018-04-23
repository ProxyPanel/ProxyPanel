<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 订单
 * Class Order
 *
 * @package App\Http\Models
 */
class Order extends Model
{
    protected $table = 'order';
    protected $primaryKey = 'oid';

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    function goods()
    {
        return $this->hasOne(Goods::class, 'id', 'goods_id');
    }

    function coupon()
    {
        return $this->hasOne(Coupon::class, 'id', 'coupon_id');
    }

    function payment()
    {
        return $this->hasOne(Payment::class, 'oid', 'oid');
    }

    function getOriginAmountAttribute($value)
    {
        return $value / 100;
    }

    function setOriginAmountAttribute($value)
    {
        return $this->attributes['origin_amount'] = $value * 100;
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