<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 节点定时Ping测速
 */
class NodePing extends Model
{

    public const UPDATED_AT = null;

    protected $table = 'node_ping';

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

}
