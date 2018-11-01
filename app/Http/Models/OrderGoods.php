<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 订单商品
 * Class OrderGoods
 *
 * @package App\Http\Models
 * @property mixed $origin_price
 * @property mixed $price
 * @property-read \App\Http\Models\Goods $goods
 * @property-read \App\Http\Models\User $user
 * @mixin \Eloquent
 */
class OrderGoods extends Model
{
    protected $table = 'order_goods';
    protected $primaryKey = 'id';

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    function goods()
    {
        return $this->hasOne(Goods::class, 'id', 'goods_id');
    }

    function getOriginPriceAttribute($value)
    {
        return $value / 100;
    }

    function setOriginPriceAttribute($value)
    {
        return $this->attributes['origin_price'] = $value * 100;
    }

    function getPriceAttribute($value)
    {
        return $value / 100;
    }

    function setPriceAttribute($value)
    {
        return $this->attributes['price'] = $value * 100;
    }
}