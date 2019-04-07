<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 商品
 * Class Goods
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Goods extends Model
{
    use SoftDeletes;
    protected $table = 'goods';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

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

    public function getTrafficLabelAttribute()
    {
        $traffic_label = flowAutoShow($this->attributes['traffic'] * 1048576);

        return $traffic_label;
    }
}