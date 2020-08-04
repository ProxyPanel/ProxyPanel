<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 节点定时Ping测速
 *
 * @property int                        $id
 * @property int                        $node_id    对应节点id
 * @property int                        $ct         电信
 * @property int                        $cu         联通
 * @property int                        $cm         移动
 * @property int                        $hk         香港
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property-read \App\Models\Node|null $node
 * @method static Builder|NodePing newModelQuery()
 * @method static Builder|NodePing newQuery()
 * @method static Builder|NodePing query()
 * @method static Builder|NodePing whereCm($value)
 * @method static Builder|NodePing whereCreatedAt($value)
 * @method static Builder|NodePing whereCt($value)
 * @method static Builder|NodePing whereCu($value)
 * @method static Builder|NodePing whereHk($value)
 * @method static Builder|NodePing whereId($value)
 * @method static Builder|NodePing whereNodeId($value)
 * @mixin \Eloquent
 */
class NodePing extends Model {
	const UPDATED_AT = null;
	protected $table = 'node_ping';

	public function node(): HasOne {
		return $this->hasOne(Node::class, 'id', 'node_id');
	}
}
