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

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            0 => '<span class="badge badge-primary">'.trans('admin.rule.group.type.on').'</span>',
            1 => '<span class="badge badge-danger">'.trans('admin.rule.group.type.off').'</span>',
        };
    }
}
