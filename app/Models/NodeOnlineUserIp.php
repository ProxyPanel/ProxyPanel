<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 节点在线用户IP信息.
 */
class NodeOnlineUserIp extends Model
{
    public $timestamps = false;
    protected $table = 'ss_node_ip';

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
