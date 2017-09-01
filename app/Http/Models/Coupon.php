<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 优惠券
 * Class Goods
 * @package App\Http\Models
 */
class Coupon extends Model
{
    protected $table = 'coupon';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'logo',
        'sn',
        'type',
        'usage',
        'amount',
        'discount',
        'available_start',
        'available_end',
        'is_del',
        'status'
    ];

}