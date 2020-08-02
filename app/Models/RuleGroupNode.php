<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 审计规则分组节点关联
 *
 * @property int                        $id
 * @property int                        $rule_group_id 规则分组ID
 * @property int                        $node_id       节点ID
 * @property \Illuminate\Support\Carbon $created_at    创建时间
 * @property \Illuminate\Support\Carbon $updated_at    最后更新时间
 * @method static Builder|RuleGroupNode newModelQuery()
 * @method static Builder|RuleGroupNode newQuery()
 * @method static Builder|RuleGroupNode query()
 * @method static Builder|RuleGroupNode whereCreatedAt($value)
 * @method static Builder|RuleGroupNode whereId($value)
 * @method static Builder|RuleGroupNode whereNodeId($value)
 * @method static Builder|RuleGroupNode whereRuleGroupId($value)
 * @method static Builder|RuleGroupNode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RuleGroupNode extends Model {
	protected $table = 'rule_group_node';
}
