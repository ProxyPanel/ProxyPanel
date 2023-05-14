<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 审计规则分组.
 */
class RuleGroup extends Model
{
    protected $table = 'rule_group';

    protected $guarded = [];

    public function getTypeLabelAttribute(): string
    {
        if ($this->attributes['type']) {
            $type_label = '<span class="badge badge-danger">'.trans('admin.rule.group.type.off').'</span>';
        } else {
            $type_label = '<span class="badge badge-primary">'.trans('admin.rule.group.type.on').'</span>';
        }

        return $type_label;
    }

    public function rules()
    {
        return $this->belongsToMany(Rule::class);
    }
}
