<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 优惠券
 * Class Goods
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Coupon extends Model
{
    use SoftDeletes;

    protected $table = 'coupon';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    // 筛选类型
    function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

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