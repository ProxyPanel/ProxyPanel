<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 节点每日流量统计
 * Class SsUserTrafficDaily
 *
 * @package App\Http\Models
 */
class SsNodeTrafficDaily extends Model
{
    protected $table = 'ss_node_traffic_daily';
    protected $primaryKey = 'id';

    public function info()
    {
        return $this->hasOne(SsNode::class, 'id', 'node_id');
    }

}