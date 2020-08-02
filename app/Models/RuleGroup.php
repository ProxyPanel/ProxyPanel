<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 审计规则分组
 *
 * @property int                        $id
 * @property int                        $type       模式：1-阻断、0-放行
 * @property string                     $name       分组名称
 * @property string|null                $rules      关联的规则ID，多个用,号分隔
 * @property string|null                $nodes      关联的节点ID，多个用,号分隔
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 最后更新时间
 * @property-read string                $type_label
 * @method static Builder|RuleGroup newModelQuery()
 * @method static Builder|RuleGroup newQuery()
 * @method static Builder|RuleGroup query()
 * @method static Builder|RuleGroup whereCreatedAt($value)
 * @method static Builder|RuleGroup whereId($value)
 * @method static Builder|RuleGroup whereName($value)
 * @method static Builder|RuleGroup whereNodes($value)
 * @method static Builder|RuleGroup whereRules($value)
 * @method static Builder|RuleGroup whereType($value)
 * @method static Builder|RuleGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RuleGroup extends Model {
	protected $table = 'rule_group';

	public function getTypeLabelAttribute(): string {
		if($this->attributes['type']){
			$type_label = '<span class="badge badge-danger">阻 断</span>';
		}else{
			$type_label = '<span class="badge badge-primary">放 行</span>';
		}
		return $type_label;
	}
}
