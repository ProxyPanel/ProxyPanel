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
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'goods_id'
    ];

}