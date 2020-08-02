<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 优惠券
 *
 * @property int                             $id
 * @property string                          $name            优惠券名称
 * @property string                          $logo            优惠券LOGO
 * @property string                          $sn              优惠券码
 * @property int                             $type            类型：1-抵用券、2-折扣券、3-充值券
 * @property int                             $usage_count     可使用次数
 * @property int                             $amount          金额，单位分
 * @property float                           $discount        折扣
 * @property int                             $rule            使用限制，单位分
 * @property int                             $available_start 有效期开始
 * @property int                             $available_end   有效期结束
 * @property int                             $status          状态：0-未使用、1-已使用、2-已失效
 * @property \Illuminate\Support\Carbon      $created_at      创建时间
 * @property \Illuminate\Support\Carbon      $updated_at      最后更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at      删除时间
 * @method static Builder|Coupon newModelQuery()
 * @method static Builder|Coupon newQuery()
 * @method static Builder|Coupon onlyTrashed()
 * @method static Builder|Coupon query()
 * @method static Builder|Coupon type($type)
 * @method static Builder|Coupon whereAmount($value)
 * @method static Builder|Coupon whereAvailableEnd($value)
 * @method static Builder|Coupon whereAvailableStart($value)
 * @method static Builder|Coupon whereCreatedAt($value)
 * @method static Builder|Coupon whereDeletedAt($value)
 * @method static Builder|Coupon whereDiscount($value)
 * @method static Builder|Coupon whereId($value)
 * @method static Builder|Coupon whereLogo($value)
 * @method static Builder|Coupon whereName($value)
 * @method static Builder|Coupon whereRule($value)
 * @method static Builder|Coupon whereSn($value)
 * @method static Builder|Coupon whereStatus($value)
 * @method static Builder|Coupon whereType($value)
 * @method static Builder|Coupon whereUpdatedAt($value)
 * @method static Builder|Coupon whereUsageCount($value)
 * @method static Builder|Coupon withTrashed()
 * @method static Builder|Coupon withoutTrashed()
 * @mixin \Eloquent
 */
class Coupon extends Model {
	use SoftDeletes;

	protected $table = 'coupon';
	protected $dates = ['deleted_at'];

	// 筛选类型
	public function scopeType($query, $type) {
		return $query->whereType($type);
	}

	public function getAmountAttribute($value) {
		return $value / 100;
	}

	public function setAmountAttribute($value): void {
		$this->attributes['amount'] = $value * 100;
	}

	public function getDiscountAttribute($value) {
		return $value * 10;
	}

	public function setDiscountAttribute($value): void {
		$this->attributes['discount'] = $value / 10;
	}

	public function getRuleAttribute($value) {
		return $value / 100;
	}

	public function setRuleAttribute($value): void {
		$this->attributes['rule'] = $value * 100;
	}
}
