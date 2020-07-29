<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 用户每日流量统计
 *
 * @property int                             $id
 * @property int                             $user_id    用户ID
 * @property int                             $node_id    节点ID，0表示统计全部节点
 * @property int                             $u          上传流量
 * @property int                             $d          下载流量
 * @property int                             $total      总流量
 * @property string|null                     $traffic    总流量（带单位）
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 最后更新时间
 * @property-read \App\Models\SsNode|null    $node
 * @method static Builder|UserTrafficDaily newModelQuery()
 * @method static Builder|UserTrafficDaily newQuery()
 * @method static Builder|UserTrafficDaily query()
 * @method static Builder|UserTrafficDaily userDaily($uid)
 * @method static Builder|UserTrafficDaily whereCreatedAt($value)
 * @method static Builder|UserTrafficDaily whereD($value)
 * @method static Builder|UserTrafficDaily whereId($value)
 * @method static Builder|UserTrafficDaily whereNodeId($value)
 * @method static Builder|UserTrafficDaily whereTotal($value)
 * @method static Builder|UserTrafficDaily whereTraffic($value)
 * @method static Builder|UserTrafficDaily whereU($value)
 * @method static Builder|UserTrafficDaily whereUpdatedAt($value)
 * @method static Builder|UserTrafficDaily whereUserId($value)
 * @mixin \Eloquent
 */
class UserTrafficDaily extends Model {
	protected $table = 'user_traffic_daily';

	public function node(): HasOne {
		return $this->hasOne(SsNode::class, 'id', 'node_id');
	}

	// 用户每天使用总流量
	public function scopeUserDaily($query, $uid) {
		return $query->whereUserId($uid)->whereNodeId(0);
	}
}
