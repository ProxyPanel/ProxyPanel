<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 节点定时Ping测速
 *
 * @property int                          $id
 * @property int                          $node_id    对应节点id
 * @property int                          $ct         电信
 * @property int                          $cu         联通
 * @property int                          $cm         移动
 * @property int                          $hk         香港
 * @property \Illuminate\Support\Carbon   $created_at 创建时间
 * @property-read \App\Models\SsNode|null $node
 * @method static Builder|SsNodePing newModelQuery()
 * @method static Builder|SsNodePing newQuery()
 * @method static Builder|SsNodePing query()
 * @method static Builder|SsNodePing whereCm($value)
 * @method static Builder|SsNodePing whereCreatedAt($value)
 * @method static Builder|SsNodePing whereCt($value)
 * @method static Builder|SsNodePing whereCu($value)
 * @method static Builder|SsNodePing whereHk($value)
 * @method static Builder|SsNodePing whereId($value)
 * @method static Builder|SsNodePing whereNodeId($value)
 * @mixin \Eloquent
 */
class SsNodePing extends Model {
	const UPDATED_AT = null;
	protected $table = 'ss_node_ping';

	public function node(): HasOne {
		return $this->hasOne(SsNode::class, 'id', 'node_id');
	}
}
