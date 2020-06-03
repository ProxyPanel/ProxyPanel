<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * SS节点在线IP信息
 *
 * @property int                        $id
 * @property int                        $node_id    节点ID
 * @property int                        $user_id    用户ID
 * @property int                        $port       端口
 * @property string                     $type       类型：all、tcp、udp
 * @property string|null                $ip         连接IP：每个IP用,号隔开
 * @property \Illuminate\Support\Carbon $created_at 上报时间
 * @property-read \App\Models\SsNode    $node
 * @property-read \App\Models\User      $user
 * @method static Builder|SsNodeIp newModelQuery()
 * @method static Builder|SsNodeIp newQuery()
 * @method static Builder|SsNodeIp query()
 * @method static Builder|SsNodeIp whereCreatedAt($value)
 * @method static Builder|SsNodeIp whereId($value)
 * @method static Builder|SsNodeIp whereIp($value)
 * @method static Builder|SsNodeIp whereNodeId($value)
 * @method static Builder|SsNodeIp wherePort($value)
 * @method static Builder|SsNodeIp whereType($value)
 * @method static Builder|SsNodeIp whereUserId($value)
 * @mixin \Eloquent
 */
class SsNodeIp extends Model {
	protected $table = 'ss_node_ip';
	protected $primaryKey = 'id';

	function node() {
		return $this->belongsTo(SsNode::class, 'node_id', 'id');
	}

	function user() {
		return $this->belongsTo(User::class, 'port', 'port');
	}
}
