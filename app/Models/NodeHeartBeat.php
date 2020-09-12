<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 节点负载信息
 */
class NodeHeartBeat extends Model
{

    public $timestamps = false;
    protected $table = 'ss_node_info';

    public function scopeRecently($query)
    {
        return $query->where('log_time', '>=', strtotime("-10 minutes"))
                     ->latest('log_time');
    }

}
