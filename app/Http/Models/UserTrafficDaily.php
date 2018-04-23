<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户每日流量统计
 * Class UserTrafficDaily
 *
 * @package App\Http\Models
 */
class UserTrafficDaily extends Model
{
    protected $table = 'user_traffic_daily';
    protected $primaryKey = 'id';

    public function node()
    {
        return $this->hasOne(SsNode::class, 'id', 'node_id');
    }
}