<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 返利日志
 *
 * @property int                             $id
 * @property int                             $user_id     用户ID
 * @property int                             $ref_user_id 推广人ID
 * @property int                             $order_id    关联订单ID
 * @property int                             $amount      消费金额，单位分
 * @property int                             $ref_amount  返利金额
 * @property int                             $status      状态：0-未提现、1-审核中、2-已提现
 * @property \Illuminate\Support\Carbon|null $created_at  创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at  最后更新时间
 * @property-read \App\Models\Order|null     $order
 * @property-read \App\Models\User|null      $ref_user
 * @property-read \App\Models\User|null      $user
 * @method static Builder|ReferralLog newModelQuery()
 * @method static Builder|ReferralLog newQuery()
 * @method static Builder|ReferralLog query()
 * @method static Builder|ReferralLog uid()
 * @method static Builder|ReferralLog whereAmount($value)
 * @method static Builder|ReferralLog whereCreatedAt($value)
 * @method static Builder|ReferralLog whereId($value)
 * @method static Builder|ReferralLog whereOrderId($value)
 * @method static Builder|ReferralLog whereRefAmount($value)
 * @method static Builder|ReferralLog whereRefUserId($value)
 * @method static Builder|ReferralLog whereStatus($value)
 * @method static Builder|ReferralLog whereUpdatedAt($value)
 * @method static Builder|ReferralLog whereUserId($value)
 * @mixin \Eloquent
 */
class ReferralLog extends Model {
	protected $table = 'referral_log';
	protected $primaryKey = 'id';

	function scopeUid($query) {
		return $query->whereRefUserId(Auth::id());
	}

	function user() {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	function ref_user() {
		return $this->hasOne(User::class, 'id', 'ref_user_id');
	}

	function order() {
		return $this->hasOne(Order::class, 'oid', 'order_id');
	}

	function getAmountAttribute($value) {
		return $value / 100;
	}

	function setAmountAttribute($value) {
		$this->attributes['amount'] = $value * 100;
	}

	function getRefAmountAttribute($value) {
		return $value / 100;
	}

	function setRefAmountAttribute($value) {
		$this->attributes['ref_amount'] = $value * 100;
	}
}
