<?php

namespace App\Http\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * 支付单
 * Class Payment
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Payment extends Model
{
    protected $table = 'payment';
    protected $primaryKey = 'id';
    protected $appends = ['status_label'];

    function scopeUid($query)
    {
        return $query->where('user_id', Auth::user()->id);
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function order()
    {
        return $this->belongsTo(Order::class, 'oid', 'oid');
    }

    function getAmountAttribute($value)
    {
        return $value / 100;
    }

    function setAmountAttribute($value)
    {
        return $this->attributes['amount'] = $value * 100;
    }

    // 订单状态
    function getStatusLabelAttribute()
    {
        switch ($this->attributes['status']) {
            case -1:
                $status_label = '支付失败';
                break;
            case 1:
                $status_label = '支付成功';
                break;
            case 0:
            default:
                $status_label = '等待支付';
        }

        return $status_label;
    }

    // 支付方式
    function getPayWayLabelAttribute()
    {
        switch ($this->attributes['pay_way']) {
            case 1:
                $pay_way_label = '微信';
                break;
            case 2:
            default:
                $pay_way_label = '支付宝';
        }

        return $pay_way_label;
    }
}
