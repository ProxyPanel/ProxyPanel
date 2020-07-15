<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 审计规则分组节点关联
 *
 * @property int                             $id
 * @property int|null                        $rule_group_id 规则分组ID
 * @property int|null                        $node_id       节点ID
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
