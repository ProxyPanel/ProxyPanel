<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 用户流量每小时统计
 *
 * @property int              $id
 * @property int              $user_id    用户ID
 * @property int              $node_id    节点ID，0表示统计全部节点
 * @property int              $u          上传流量
 * @property int              $d          下载流量
 * @property int              $total      总流量
 * @property string|null      $traffic    总流量（带单位）
 * @property Carbon|null      $created_at 创建时间
 * @property Carbon|null      $updated_at 最后更新时间
 * @property-read SsNode|null $node
 * @method static Builder|UserTrafficHourly newModelQuery()
 * @method static Builder|UserTrafficHourly newQuery()
 * @method static Builder|UserTrafficHourly query()
 * @method static Builder|UserTrafficHourly whereCreatedAt($value)
 * @method static Builder|UserTrafficHourly whereD($value)
 * @method static Builder|UserTrafficHourly whereId($value)
 * @method static Builder|UserTrafficHourly whereNodeId($value)
 * @method static Builder|UserTrafficHourly whereTotal($value)
 * @method static Builder|UserTrafficHourly whereTraffic($value)
 * @method static Builder|UserTrafficHourly whereU($value)
 * @method static Builder|UserTrafficHourly whereUpdatedAt($value)
 * @method static Builder|UserTrafficHourly whereUserId($value)
 * @mixin Eloquent
 */
class UserTrafficHourly extends Model {
	protected $table = 'user_traffic_hourly';
	protected $primaryKey = 'id';

	function node() {
		return $this->hasOne(SsNode::class, 'id', 'node_id');
	}
}
