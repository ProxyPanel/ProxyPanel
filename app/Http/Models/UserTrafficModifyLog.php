<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户流量变动记录
 * Class UserTrafficModifyLog
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class UserTrafficModifyLog extends Model
{
    protected $table = 'user_traffic_modify_log';
    protected $primaryKey = 'id';

    // 关联账号
    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    // 关联订单
    function order()
    {
        return $this->hasOne(Order::class, 'oid', 'order_id');
    }

    function getBeforeAttribute($value)
    {
        return $this->attributes['before'] = flowAutoShow($value);
    }

    function getAfterAttribute($value)
    {
        return $this->attributes['after'] = flowAutoShow($value);
    }

}