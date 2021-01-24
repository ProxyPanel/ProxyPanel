<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
            1 => '正则表达式',
            2 => '域 名',
            3 => 'I P',
            4 => '协 议',
        ][$this->attributes['type']] ?? '未 知';
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

    public function rule_groups()
    {
        return $this->belongsToMany(RuleGroup::class);
    }
}
