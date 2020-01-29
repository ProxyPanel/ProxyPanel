<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 节点每日流量统计
 * Class SsUserTrafficDaily
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property int         $node_id    节点ID
 * @property int         $u          上传流量
 * @property int         $d          下载流量
 * @property int         $total      总流量
 * @property string|null $traffic    总流量（带单位）
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 最后更新时间
 * @property-read SsNode $info
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
 * @method static Builder|SsNodeTrafficDaily whereUpdatedAt($value)
 */
class SsNodeTrafficDaily extends Model
{
	protected $table = 'ss_node_traffic_daily';
	protected $primaryKey = 'id';

	function info()
	{
		return $this->hasOne(SsNode::class, 'id', 'node_id');
	}

}