<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 用户流量变动记录
 *
 * @property int                         $id
 * @property int                         $user_id     用户ID
 * @property int                         $order_id    发生的订单ID
 * @property int                         $before      操作前流量
 * @property int                         $after       操作后流量
 * @property string                      $description 描述
 * @property \Illuminate\Support\Carbon  $created_at  创建时间
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\User|null  $user
 * @method static Builder|UserTrafficModifyLog newModelQuery()
 * @method static Builder|UserTrafficModifyLog newQuery()
 * @method static Builder|UserTrafficModifyLog query()
 * @method static Builder|UserTrafficModifyLog whereAfter($value)
 * @method static Builder|UserTrafficModifyLog whereBefore($value)
 * @method static Builder|UserTrafficModifyLog whereCreatedAt($value)
 * @method static Builder|UserTrafficModifyLog whereDescription($value)
 * @method static Builder|UserTrafficModifyLog whereId($value)
 * @method static Builder|UserTrafficModifyLog whereOrderId($value)
 * @method static Builder|UserTrafficModifyLog whereUserId($value)
 * @mixin \Eloquent
 */
class UserTrafficModifyLog extends Model {
	const UPDATED_AT = null;
	protected $table = 'user_traffic_modify_log';

	// 关联账号
	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	// 关联订单
	public function order(): HasOne {
		return $this->hasOne(Order::class, 'oid', 'order_id');
	}

	public function getBeforeAttribute($value) {
		return $this->attributes['before'] = flowAutoShow($value);
	}

	public function getAfterAttribute($value) {
		return $this->attributes['after'] = flowAutoShow($value);
	}
}
