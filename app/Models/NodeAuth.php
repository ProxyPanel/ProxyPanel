<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NodeAuth
 *
 * @property int                             $id
 * @property int                             $node_id    授权节点ID
 * @property string|null                     $key        认证KEY
 * @property string|null                     $secret     通信密钥
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 最后更新时间
 * @property-read \App\Models\SsNode|null    $node
 * @method static Builder|NodeAuth newModelQuery()
 * @method static Builder|NodeAuth newQuery()
 * @method static Builder|NodeAuth query()
 * @method static Builder|NodeAuth whereCreatedAt($value)
 * @method static Builder|NodeAuth whereId($value)
 * @method static Builder|NodeAuth whereKey($value)
 * @method static Builder|NodeAuth whereNodeId($value)
 * @method static Builder|NodeAuth whereSecret($value)
 * @method static Builder|NodeAuth whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NodeAuth extends Model {
	protected $table = 'node_auth';
	protected $primaryKey = 'id';

	function node() {
		return $this->hasOne(SsNode::class, 'id', 'node_id');
	}
}
