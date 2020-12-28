<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 节点负载信息.
 */
class NodeHeartbeat extends Model
{
    public $timestamps = false;
    protected $table = 'node_heartbeat';

    public function scopeRecently($query)
    {
        return $query->where('log_time', '>=', strtotime('-10 minutes'))->latest('log_time');
    }
}
