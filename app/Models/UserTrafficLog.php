<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户流量记录
 *
 * @property int                     $id
 * @property int                     $user_id  用户ID
 * @property int                     $node_id  节点ID
 * @property int                     $u        上传流量
 * @property int                     $d        下载流量
 * @property float                   $rate     倍率
 * @property string                  $traffic  产生流量
 * @property int                     $log_time 记录时间
 * @property-read \App\Models\SsNode $node
 * @property-read \App\Models\User   $user
 * @method static Builder|UserTrafficLog newModelQuery()
 * @method static Builder|UserTrafficLog newQuery()
 * @method static Builder|UserTrafficLog query()
 * @method static Builder|UserTrafficLog whereD($value)
 * @method static Builder|UserTrafficLog whereId($value)
 * @method static Builder|UserTrafficLog whereLogTime($value)
 * @method static Builder|UserTrafficLog whereNodeId($value)
 * @method static Builder|UserTrafficLog whereRate($value)
 * @method static Builder|UserTrafficLog whereTraffic($value)
 * @method static Builder|UserTrafficLog whereU($value)
 * @method static Builder|UserTrafficLog whereUserId($value)
 * @mixin \Eloquent
 */
class UserTrafficLog extends Model {
	public $timestamps = false;
	protected $table = 'user_traffic_log';
	protected $primaryKey = 'id';

	// 关联账号

	function user() {
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	// 关联节点
	function node() {
		return $this->belongsTo(SsNode::class, 'node_id', 'id');
	}

}
