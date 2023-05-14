<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 节点每日流量统计
 */
class NodeHourlyDataFlow extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'node_hourly_data_flow';

    protected $guarded = [];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
