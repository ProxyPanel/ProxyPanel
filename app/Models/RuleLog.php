<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 触发审计规则日志.
 */
class RuleLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'rule_log';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }
}
