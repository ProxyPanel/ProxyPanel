<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 订单商品
 * Class OrderGoods
 * @package App\Http\Models
 */
class OrderGoods extends Model
{
    protected $table = 'order_goods';
    protected $primaryKey = 'id';
    protected $fillable = [
        'oid',
        'orderId',
        'user_id',
        'goods_id',
        'num',
        'original_price',
        'price',
        'is_expire'
    ];

    function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    function goods() {
        return $this->hasOne(Goods::class, 'id', 'goods_id');
    }
}