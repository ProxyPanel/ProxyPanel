<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 触发审计规则日志
 *
 * @property int                          $id
 * @property int                          $user_id    用户ID
 * @property int                          $node_id    节点ID
 * @property int                          $rule_id    规则ID，0表示白名单模式下访问访问了非规则允许的网址
 * @property string                       $reason     触发原因
 * @property \Illuminate\Support\Carbon   $created_at 创建时间
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
 * @method static Builder|RuleLog whereUserId($value)
 * @mixin \Eloquent
 */
class RuleLog extends Model {
	const UPDATED_AT = null;
	protected $table = 'rule_log';

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function node(): HasOne {
		return $this->hasOne(SsNode::class, 'id', 'node_id');
	}

	public function rule(): HasOne {
		return $this->hasOne(Rule::class, 'id', 'rule_id');
	}
}
