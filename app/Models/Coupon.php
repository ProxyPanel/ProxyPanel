<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 优惠券.
 */
class Coupon extends Model
{
    use SoftDeletes;

    protected $table = 'coupon';
    protected $dates = ['deleted_at'];
    protected $fillable = ['usable_times', 'status'];

    // 筛选类型
    public function scopeType($query, $type)
    {
        return $query->whereType($type);
    }
}
