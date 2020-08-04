<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 节点负载信息
 *
 * @property int    $id
 * @property int    $node_id  节点ID
 * @property int    $uptime   后端存活时长，单位秒
 * @property string $load     负载
 * @property int    $log_time 记录时间
 * @method static Builder|NodeInfo newModelQuery()
 * @method static Builder|NodeInfo newQuery()
 * @method static Builder|NodeInfo query()
 * @method static Builder|NodeInfo whereId($value)
 * @method static Builder|NodeInfo whereLoad($value)
 * @method static Builder|NodeInfo whereLogTime($value)
 * @method static Builder|NodeInfo whereNodeId($value)
 * @method static Builder|NodeInfo whereUptime($value)
 * @mixin \Eloquent
 */
class NodeInfo extends Model {
	public $timestamps = false;
	protected $table = 'ss_node_info';
}
