<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户流量每小时统计
 * Class Article
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class UserTrafficHourly extends Model
{
    protected $table = 'user_traffic_hourly';
    protected $primaryKey = 'id';

    function node()
    {
        return $this->hasOne(SsNode::class, 'id', 'node_id');
    }
}