<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 优惠券
 * Class Goods
 *
 * @package App\Http\Models
 */
class Coupon extends Model
{
    protected $table = 'coupon';
    protected $primaryKey = 'id';


    function getAmountAttribute($value)
    {
        return $value / 100;
    }

    function setAmountAttribute($value)
    {
        $this->attributes['amount'] = $value * 100;
    }

    function getDiscountAttribute($value)
    {
        return $value * 10;
    }

    function setDiscountAttribute($value)
    {
        $this->attributes['discount'] = $value / 10;
    }
}