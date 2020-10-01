<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 节点用户在线情况
 */
class NodeOnlineLog extends Model
{
    public $timestamps = false;
    protected $table = 'ss_node_online_log';
}
