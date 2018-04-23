<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 节点每日流量统计
 * Class SsUserTrafficHourly
 *
 * @package App\Http\Models
 */
class SsNodeTrafficHourly extends Model
{
    protected $table = 'ss_node_traffic_hourly';
    protected $primaryKey = 'id';

    public function info()
    {
        return $this->hasOne(SsNode::class, 'id', 'node_id');
    }
}