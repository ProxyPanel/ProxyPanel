<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 触发审计规则日志
 *
 * @property int                          $id
 * @property int                          $user_id 用户ID
 * @property int                          $node_id 节点ID
 * @property int                          $rule_id 规则ID，0表示白名单模式下访问访问了非规则允许的网址
 * @property string                       $reason  触发原因
 * @property \Illuminate\Support\Carbon   $created_at
 * @property \Illuminate\Support\Carbon   $updated_at
 * @property-read \App\Models\SsNode|null $node
 * @property-read \App\Models\Rule|null   $rule
 * @property-read \App\Models\User|null   $user
 * @method static Builder|RuleLog newModelQuery()
 * @method static Builder|RuleLog newQuery()
 * @method static Builder|RuleLog query()
 * @method static Builder|RuleLog whereCreatedAt($value)
 * @method static Builder|RuleLog whereId($value)
 * @method static Builder|RuleLog whereNodeId($value)
 * @method static Builder|RuleLog whereReason($value)
 * @method static Builder|RuleLog whereRuleId($value)
 * @method static Builder|RuleLog whereUpdatedAt($value)
 * @method static Builder|RuleLog whereUserId($value)
 * @mixin \Eloquent
 */
class RuleLog extends Model {
	protected $table = 'rule_log';
	protected $primaryKey = 'id';

	function user() {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	function node() {
		return $this->hasOne(SsNode::class, 'id', 'node_id');
	}

	function rule() {
		return $this->hasOne(Rule::class, 'id', 'rule_id');
	}
}
