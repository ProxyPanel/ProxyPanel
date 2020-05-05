<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 节点定时Ping测速
 *
 * @property int              $id
 * @property int              $node_id 对应节点id
 * @property int              $ct      电信
 * @property int              $cu      联通
 * @property int              $cm      移动
 * @property int              $hk      香港
 * @property Carbon           $created_at
 * @property Carbon           $updated_at
 * @property-read SsNode|null $node
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
 * @method static Builder|SsNodePing whereUpdatedAt($value)
 * @mixin Eloquent
 */
class SsNodePing extends Model {
	protected $table = 'ss_node_ping';
	protected $primaryKey = 'id';

	public function node() {
		return $this->hasOne(SsNode::class, 'id', 'node_id');
	}
}
