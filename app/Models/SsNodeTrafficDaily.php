<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 节点每日流量统计
 *
 * @property int                          $id
 * @property int                          $node_id    节点ID
 * @property int                          $u          上传流量
 * @property int                          $d          下载流量
 * @property int                          $total      总流量
 * @property string|null                  $traffic    总流量（带单位）
 * @property \Illuminate\Support\Carbon   $created_at 创建时间
 * @property-read \App\Models\SsNode|null $info
 * @method static Builder|SsNodeTrafficDaily newModelQuery()
 * @method static Builder|SsNodeTrafficDaily newQuery()
 * @method static Builder|SsNodeTrafficDaily query()
 * @method static Builder|SsNodeTrafficDaily whereCreatedAt($value)
 * @method static Builder|SsNodeTrafficDaily whereD($value)
 * @method static Builder|SsNodeTrafficDaily whereId($value)
 * @method static Builder|SsNodeTrafficDaily whereNodeId($value)
 * @method static Builder|SsNodeTrafficDaily whereTotal($value)
 * @method static Builder|SsNodeTrafficDaily whereTraffic($value)
 * @method static Builder|SsNodeTrafficDaily whereU($value)
 * @mixin \Eloquent
 */
class SsNodeTrafficDaily extends Model {
	const UPDATED_AT = null;
	protected $table = 'ss_node_traffic_daily';

	public function info(): HasOne {
		return $this->hasOne(SsNode::class, 'id', 'node_id');
	}
}
