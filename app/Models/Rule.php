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
        switch ($this->attributes['type']) {
            case 1:
                $type_label = '正则表达式';
                break;
            case 2:
                $type_label = '域 名';
                break;
            case 3:
                $type_label = 'I P';
                break;
            case 4:
                $type_label = '协 议';
                break;
            default:
                $type_label = '未 知';
        }

        return $type_label;
    }

    public function getTypeApiLabelAttribute(): string
    {
        switch ($this->attributes['type']) {
            case 1:
                $type_api_label = 'reg';
                break;
            case 2:
                $type_api_label = 'domain';
                break;
            case 3:
                $type_api_label = 'ip';
                break;
            case 4:
                $type_api_label = 'protocol';
                break;
            default:
                $type_api_label = 'unknown';
        }

        return $type_api_label;
    }

    public function rule_groups()
    {
        return $this->belongsToMany(RuleGroup::class);
    }
}
