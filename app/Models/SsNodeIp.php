<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SS节点在线IP信息
 *
 * @property int                     $id
 * @property int                     $node_id    节点ID
 * @property int                     $user_id    用户ID
 * @property int                     $port       端口
 * @property string                  $type       类型：all、tcp、udp
 * @property string|null             $ip         连接IP：每个IP用,号隔开
 * @property int                     $created_at 上报时间
 * @property-read \App\Models\SsNode $node
 * @property-read \App\Models\User   $user
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
	public $timestamps = false;
	protected $table = 'ss_node_ip';

	public function node(): BelongsTo {
		return $this->belongsTo(SsNode::class, 'node_id', 'id');
	}

	public function user(): BelongsTo {
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}
