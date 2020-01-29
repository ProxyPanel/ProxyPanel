<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * SS节点用户在线情况
 * Class SsNodeOnlineLog
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int $id
 * @property int $node_id     节点ID
 * @property int $online_user 在线用户数
 * @property int $log_time    记录时间
 * @method static Builder|SsNodeOnlineLog newModelQuery()
 * @method static Builder|SsNodeOnlineLog newQuery()
 * @method static Builder|SsNodeOnlineLog query()
 * @method static Builder|SsNodeOnlineLog whereId($value)
 * @method static Builder|SsNodeOnlineLog whereLogTime($value)
 * @method static Builder|SsNodeOnlineLog whereNodeId($value)
 * @method static Builder|SsNodeOnlineLog whereOnlineUser($value)
 */
class SsNodeOnlineLog extends Model
{
	public $timestamps = FALSE;
	protected $table = 'ss_node_online_log';
	protected $primaryKey = 'id';

}