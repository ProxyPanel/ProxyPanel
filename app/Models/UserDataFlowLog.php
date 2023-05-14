<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 用户流量记录.
 */
class UserDataFlowLog extends Model
{
    public $timestamps = false;

    protected $table = 'user_traffic_log';

    protected $guarded = [];

    // 关联账号
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 关联节点
    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
