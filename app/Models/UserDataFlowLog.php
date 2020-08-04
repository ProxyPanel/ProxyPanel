<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 用户流量记录
 *
 * @property int                   $id
 * @property int                   $user_id  用户ID
 * @property int                   $node_id  节点ID
 * @property int                   $u        上传流量
 * @property int                   $d        下载流量
 * @property float                 $rate     倍率
 * @property string                $traffic  产生流量
 * @property int                   $log_time 记录时间
 * @property-read \App\Models\Node $node
 * @property-read \App\Models\User $user
 * @method static Builder|UserDataFlowLog newModelQuery()
 * @method static Builder|UserDataFlowLog newQuery()
 * @method static Builder|UserDataFlowLog query()
 * @method static Builder|UserDataFlowLog whereD($value)
 * @method static Builder|UserDataFlowLog whereId($value)
 * @method static Builder|UserDataFlowLog whereLogTime($value)
 * @method static Builder|UserDataFlowLog whereNodeId($value)
 * @method static Builder|UserDataFlowLog whereRate($value)
 * @method static Builder|UserDataFlowLog whereTraffic($value)
 * @method static Builder|UserDataFlowLog whereU($value)
 * @method static Builder|UserDataFlowLog whereUserId($value)
 * @mixin \Eloquent
 */
class UserDataFlowLog extends Model {
	public $timestamps = false;
	protected $table = 'user_traffic_log';

	// 关联账号
	public function user(): BelongsTo {
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	// 关联节点
	public function node(): BelongsTo {
		return $this->belongsTo(Node::class, 'node_id', 'id');
	}
}
