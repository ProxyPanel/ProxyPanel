<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 用户流量每小时统计
 *
 * @property int                        $id
 * @property int                        $user_id    用户ID
 * @property int                        $node_id    节点ID，0表示统计全部节点
 * @property int                        $u          上传流量
 * @property int                        $d          下载流量
 * @property int                        $total      总流量
 * @property string|null                $traffic    总流量（带单位）
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property-read \App\Models\Node|null $node
 * @method static Builder|UserHourlyDataFlow newModelQuery()
 * @method static Builder|UserHourlyDataFlow newQuery()
 * @method static Builder|UserHourlyDataFlow query()
 * @method static Builder|UserHourlyDataFlow userHourly($uid)
 * @method static Builder|UserHourlyDataFlow whereCreatedAt($value)
 * @method static Builder|UserHourlyDataFlow whereD($value)
 * @method static Builder|UserHourlyDataFlow whereId($value)
 * @method static Builder|UserHourlyDataFlow whereNodeId($value)
 * @method static Builder|UserHourlyDataFlow whereTotal($value)
 * @method static Builder|UserHourlyDataFlow whereTraffic($value)
 * @method static Builder|UserHourlyDataFlow whereU($value)
 * @method static Builder|UserHourlyDataFlow whereUserId($value)
 * @mixin \Eloquent
 */
class UserHourlyDataFlow extends Model {
	const UPDATED_AT = null;
	protected $table = 'user_hourly_data_flow';

	public function node(): HasOne {
		return $this->hasOne(Node::class, 'id', 'node_id');
	}

	// 用户每时使用总流量
	public function scopeUserHourly($query, $uid) {
		return $query->whereUserId($uid)->whereNodeId(0);
	}
}
