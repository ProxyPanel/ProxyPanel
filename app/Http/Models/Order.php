<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 订单
 * Class Order
 *
 * @package App\Http\Models
 */
class Order extends Model
{
    protected $table = 'order';
    protected $primaryKey = 'oid';

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    function goods()
    {
        return $this->hasOne(Goods::class, 'id', 'goods_id');
    }

    function coupon()
    {
        return $this->hasOne(Coupon::class, 'id', 'coupon_id');
    }

    function payment()
    {
        return $this->hasOne(Payment::class, 'oid', 'oid');
    }

    function getOriginAmountAttribute($value)
    {
        return $value / 100;
    }

    function setOriginAmountAttribute($value)
    {
        return $this->attributes['origin_amount'] = $value * 100;
    }

    function getAmountAttribute($value)
    {
        return $value / 100;
    }

    function setAmountAttribute($value)
    {
        return $this->attributes['amount'] = $value * 100;
    }

    public function getStatusLabelAttribute()
    {
        switch ($this->attributes['status']) {
            case -1:
                $status_label = '已关闭';
                break;
            case 1:
                $status_label = '已支付待确认';
                break;
            case 2:
                $status_label = '已完成';
                break;
            case 0:
            default:
                $status_label = '待支付';
                break;
        }

        return $status_label;
    }
}