<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 节点在线用户IP信息
 *
 * @property int                   $id
 * @property int                   $node_id    节点ID
 * @property int                   $user_id    用户ID
 * @property int                   $port       端口
 * @property string                $type       类型：all、tcp、udp
 * @property string|null           $ip         连接IP：每个IP用,号隔开
 * @property int                   $created_at 上报时间
 * @property-read \App\Models\Node $node
 * @property-read \App\Models\User $user
 * @method static Builder|NodeOnlineUserIp newModelQuery()
 * @method static Builder|NodeOnlineUserIp newQuery()
 * @method static Builder|NodeOnlineUserIp query()
 * @method static Builder|NodeOnlineUserIp whereCreatedAt($value)
 * @method static Builder|NodeOnlineUserIp whereId($value)
 * @method static Builder|NodeOnlineUserIp whereIp($value)
 * @method static Builder|NodeOnlineUserIp whereNodeId($value)
 * @method static Builder|NodeOnlineUserIp wherePort($value)
 * @method static Builder|NodeOnlineUserIp whereType($value)
 * @method static Builder|NodeOnlineUserIp whereUserId($value)
 * @mixin \Eloquent
 */
class NodeOnlineUserIp extends Model {
	public $timestamps = false;
	protected $table = 'ss_node_ip';

	public function node(): BelongsTo {
		return $this->belongsTo(Node::class, 'node_id', 'id');
	}

	public function user(): BelongsTo {
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}
