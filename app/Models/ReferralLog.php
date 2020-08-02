<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 返利日志
 *
 * @property int                         $id
 * @property int                         $user_id     用户ID
 * @property int                         $ref_user_id 推广人ID
 * @property int                         $order_id    关联订单ID
 * @property int                         $amount      消费金额，单位分
 * @property int                         $ref_amount  返利金额
 * @property int                         $status      状态：0-未提现、1-审核中、2-已提现
 * @property \Illuminate\Support\Carbon  $created_at  创建时间
 * @property \Illuminate\Support\Carbon  $updated_at  最后更新时间
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\User|null  $ref_user
 * @property-read \App\Models\User|null  $user
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

	public function scopeUid($query) {
		return $query->whereRefUserId(Auth::id());
	}

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function ref_user(): HasOne {
		return $this->hasOne(User::class, 'id', 'ref_user_id');
	}

	public function order(): HasOne {
		return $this->hasOne(Order::class, 'oid', 'order_id');
	}

	public function getAmountAttribute($value) {
		return $value / 100;
	}

	public function setAmountAttribute($value): void {
		$this->attributes['amount'] = $value * 100;
	}

	public function getRefAmountAttribute($value) {
		return $value / 100;
	}

	public function setRefAmountAttribute($value): void {
		$this->attributes['ref_amount'] = $value * 100;
	}
}
