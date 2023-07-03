<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 节点负载信息.
 */
class NodeHeartbeat extends Model
{
    public $timestamps = false;

    protected $table = 'node_heartbeat';

    protected $guarded = [];

    public function scopeRecently(Builder $query): Builder
    {
        return $query->where('log_time', '>=', strtotime(config('tasks.recently_heartbeat')))->latest('log_time');
    }
}
