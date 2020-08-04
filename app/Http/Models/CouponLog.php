<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 优惠券使用日志
 * Class Goods
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class CouponLog extends Model
{
    protected $table = 'coupon_log';
    protected $primaryKey = 'id';

}