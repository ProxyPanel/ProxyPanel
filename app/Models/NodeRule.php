<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 节点审计规则关联
 *
 * @property int                        $id
 * @property int                        $node_id  节点ID
 * @property int                        $rule_id  审计规则ID
 * @property int                        $is_black 是否黑名单模式：0-不是、1-是
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static Builder|NodeRule newModelQuery()
 * @method static Builder|NodeRule newQuery()
 * @method static Builder|NodeRule query()
 * @method static Builder|NodeRule whereCreatedAt($value)
 * @method static Builder|NodeRule whereId($value)
 * @method static Builder|NodeRule whereIsBlack($value)
 * @method static Builder|NodeRule whereNodeId($value)
 * @method static Builder|NodeRule whereRuleId($value)
 * @method static Builder|NodeRule whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NodeRule extends Model {
	protected $table = 'node_rule';
	protected $primaryKey = 'id';
}
