<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 节点用户在线情况
 *
 * @property int $id
 * @property int $node_id     节点ID
 * @property int $online_user 在线用户数
 * @property int $log_time    记录时间
 * @method static Builder|NodeOnlineLog newModelQuery()
 * @method static Builder|NodeOnlineLog newQuery()
 * @method static Builder|NodeOnlineLog query()
 * @method static Builder|NodeOnlineLog whereId($value)
 * @method static Builder|NodeOnlineLog whereLogTime($value)
 * @method static Builder|NodeOnlineLog whereNodeId($value)
 * @method static Builder|NodeOnlineLog whereOnlineUser($value)
 * @mixin \Eloquent
 */
class NodeOnlineLog extends Model {
	public $timestamps = false;
	protected $table = 'ss_node_online_log';
}
