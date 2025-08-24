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

    protected $guarded = [];
}
