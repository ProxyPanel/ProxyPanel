<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 注册时的验证激活地址
 */
class Verify extends Model
{
    protected $table = 'verify';

    // 筛选类型
    public function scopeType($query, $type)
    {
        return $query->whereType($type);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
