<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 节点在线用户IP信息.
 */
class NodeOnlineIp extends Model
{
    public $timestamps = false;

    protected $table = 'node_online_ip';

    protected $guarded = [];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
