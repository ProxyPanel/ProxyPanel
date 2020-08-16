<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 审计规则分组
 */
class RuleGroup extends Model {
	protected $table = 'rule_group';
	protected $casts = [
		'rules' => 'array',
		'nodes' => 'array'
	];

	public function getTypeLabelAttribute(): string {
		if($this->attributes['type']){
			$type_label = '<span class="badge badge-danger">阻 断</span>';
		}else{
			$type_label = '<span class="badge badge-primary">放 行</span>';
		}
		return $type_label;
	}
}
