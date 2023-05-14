<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 节点授权密钥.
 */
class NodeAuth extends Model
{
    protected $table = 'node_auth';

    protected $guarded = [];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
