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
 * @method static Builder|NodeHourlyDataFlow newModelQuery()
 * @method static Builder|NodeHourlyDataFlow newQuery()
 * @method static Builder|NodeHourlyDataFlow query()
 * @method static Builder|NodeHourlyDataFlow whereCreatedAt($value)
 * @method static Builder|NodeHourlyDataFlow whereD($value)
 * @method static Builder|NodeHourlyDataFlow whereId($value)
 * @method static Builder|NodeHourlyDataFlow whereNodeId($value)
 * @method static Builder|NodeHourlyDataFlow whereTotal($value)
 * @method static Builder|NodeHourlyDataFlow whereTraffic($value)
 * @method static Builder|NodeHourlyDataFlow whereU($value)
 * @mixin \Eloquent
 */
class NodeHourlyDataFlow extends Model {
	const UPDATED_AT = null;
	protected $table = 'node_hourly_data_flow';

	public function info(): HasOne {
		return $this->hasOne(Node::class, 'id', 'node_id');
	}
}
