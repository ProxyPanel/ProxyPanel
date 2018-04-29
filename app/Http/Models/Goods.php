<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 商品
 * Class Goods
 *
 * @package App\Http\Models
 */
class Goods extends Model
{
    protected $table = 'goods';
    protected $primaryKey = 'id';

    function label()
    {
        return $this->hasMany(GoodsLabel::class, 'goods_id', 'id');
    }

    function getPriceAttribute($value)
    {
        return $value / 100;
    }

    function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }
}