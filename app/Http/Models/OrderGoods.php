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

    function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    function goods() {
        return $this->hasOne(Goods::class, 'id', 'goods_id');
    }
}