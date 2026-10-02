<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 审计规则分组.
 */
class RuleGroup extends Model
{
    protected $table = 'rule_group';

    protected $guarded = [];

    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class);
    }

    /**
     * 审计组类型的语义值：徽标样式交给 x-badge。
     */
    public function getTypeBadgeAttribute(): array
    {
        return match ($this->type) {
            0 => ['type' => 'primary', 'text' => trans('admin.rule.group.type.on')],
            1 => ['type' => 'danger', 'text' => trans('admin.rule.group.type.off')],
        };
    }
}
