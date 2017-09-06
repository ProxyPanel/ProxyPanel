<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 订单
 * Class Order
 * @package App\Http\Models
 */
class Order extends Model
{
    protected $table = 'order';
    protected $primaryKey = 'oid';
    protected $fillable = [
        'orderId',
        'user_id',
        'coupon_id',
        'totalOriginalPrice',
        'totalPrice',
        'status'
    ];

    function goodsList() {
        return $this->hasMany(OrderGoods::class, 'oid', 'oid');
    }
}