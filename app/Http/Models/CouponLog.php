<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 优惠券使用日志
 * Class Goods
 * @package App\Http\Models
 */
class CouponLog extends Model
{
    protected $table = 'coupon_log';
    protected $primaryKey = 'id';
    protected $fillable = [
        'coupon_id',
        'goods_id',
        'order_id'
    ];

}