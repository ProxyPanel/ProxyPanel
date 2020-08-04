<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 用户每日流量统计
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
 * @method static Builder|UserDailyDataFlow newModelQuery()
 * @method static Builder|UserDailyDataFlow newQuery()
 * @method static Builder|UserDailyDataFlow query()
 * @method static Builder|UserDailyDataFlow userDaily($uid)
 * @method static Builder|UserDailyDataFlow whereCreatedAt($value)
 * @method static Builder|UserDailyDataFlow whereD($value)
 * @method static Builder|UserDailyDataFlow whereId($value)
 * @method static Builder|UserDailyDataFlow whereNodeId($value)
 * @method static Builder|UserDailyDataFlow whereTotal($value)
 * @method static Builder|UserDailyDataFlow whereTraffic($value)
 * @method static Builder|UserDailyDataFlow whereU($value)
 * @method static Builder|UserDailyDataFlow whereUserId($value)
 * @mixin \Eloquent
 */
class UserDailyDataFlow extends Model {
	const UPDATED_AT = null;
	protected $table = 'user_daily_data_flow';

	public function node(): HasOne {
		return $this->hasOne(Node::class, 'id', 'node_id');
	}

	// 用户每天使用总流量
	public function scopeUserDaily($query, $uid) {
		return $query->whereUserId($uid)->whereNodeId(0);
	}
}
