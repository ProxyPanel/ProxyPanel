<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 审计规则.
 */
class Rule extends Model
{
    public $timestamps = false;

    protected $table = 'rule';

    protected $guarded = [];

    public function getTypeLabelAttribute(): string
    {
        return [
            1 => trans('admin.rule.type.reg'),
            2 => trans('admin.rule.type.domain'),
            3 => trans('admin.rule.type.ip'),
            4 => trans('admin.rule.type.protocol'),
        ][$this->attributes['type']] ?? trans('common.status.unknown');
    }

    public function getTypeApiLabelAttribute(): string
    {
        return [
            1 => 'reg',
            2 => 'domain',
            3 => 'ip',
            4 => 'protocol',
        ][$this->attributes['type']] ?? 'unknown';
    }

    public function rule_groups(): BelongsToMany
    {
        return $this->belongsToMany(RuleGroup::class);
    }
}
