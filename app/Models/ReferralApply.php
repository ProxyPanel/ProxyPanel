<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 返利申请
 *
 * @property int                             $id
 * @property int                             $user_id    用户ID
 * @property int                             $before     操作前可提现金额，单位分
 * @property int                             $after      操作后可提现金额，单位分
 * @property int                             $amount     本次提现金额，单位分
 * @property string                          $link_logs  关联返利日志ID，例如：1,3,4
 * @property int                             $status     状态：-1-驳回、0-待审核、1-审核通过待打款、2-已打款
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 最后更新时间
 * @property-read \App\Models\User|null      $User
 * @method static Builder|ReferralApply newModelQuery()
 * @method static Builder|ReferralApply newQuery()
 * @method static Builder|ReferralApply query()
 * @method static Builder|ReferralApply uid()
 * @method static Builder|ReferralApply whereAfter($value)
 * @method static Builder|ReferralApply whereAmount($value)
 * @method static Builder|ReferralApply whereBefore($value)
 * @method static Builder|ReferralApply whereCreatedAt($value)
 * @method static Builder|ReferralApply whereId($value)
 * @method static Builder|ReferralApply whereLinkLogs($value)
 * @method static Builder|ReferralApply whereStatus($value)
 * @method static Builder|ReferralApply whereUpdatedAt($value)
 * @method static Builder|ReferralApply whereUserId($value)
 * @mixin \Eloquent
 */
class ReferralApply extends Model {
	protected $table = 'referral_apply';

	public function scopeUid($query) {
		return $query->whereUserId(Auth::id());
	}

	public function User(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function getBeforeAttribute($value) {
		return $value / 100;
	}

	public function setBeforeAttribute($value): void {
		$this->attributes['before'] = $value * 100;
	}

	public function getAfterAttribute($value) {
		return $value / 100;
	}

	public function setAfterAttribute($value): void {
		$this->attributes['after'] = $value * 100;
	}

	public function getAmountAttribute($value) {
		return $value / 100;
	}

	public function setAmountAttribute($value): void {
		$this->attributes['amount'] = $value * 100;
	}
}
