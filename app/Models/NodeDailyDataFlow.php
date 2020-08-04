<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 节点每日流量统计
 *
 * @property int                        $id
 * @property int                        $node_id    节点ID
 * @property int                        $u          上传流量
 * @property int                        $d          下载流量
 * @property int                        $total      总流量
 * @property string|null                $traffic    总流量（带单位）
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property-read \App\Models\Node|null $info
 * @method static Builder|NodeDailyDataFlow newModelQuery()
 * @method static Builder|NodeDailyDataFlow newQuery()
 * @method static Builder|NodeDailyDataFlow query()
 * @method static Builder|NodeDailyDataFlow whereCreatedAt($value)
 * @method static Builder|NodeDailyDataFlow whereD($value)
 * @method static Builder|NodeDailyDataFlow whereId($value)
 * @method static Builder|NodeDailyDataFlow whereNodeId($value)
 * @method static Builder|NodeDailyDataFlow whereTotal($value)
 * @method static Builder|NodeDailyDataFlow whereTraffic($value)
 * @method static Builder|NodeDailyDataFlow whereU($value)
 * @mixin \Eloquent
 */
class NodeDailyDataFlow extends Model {
	const UPDATED_AT = null;
	protected $table = 'node_daily_data_flow';

	public function info(): HasOne {
		return $this->hasOne(Node::class, 'id', 'node_id');
	}
}
