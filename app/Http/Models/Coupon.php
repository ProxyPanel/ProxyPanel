<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * 优惠券
 * Class Goods
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property string      $name            优惠券名称
 * @property string      $logo            优惠券LOGO
 * @property string      $sn              优惠券码
 * @property int         $type            类型：1-现金券、2-折扣券、3-充值券
 * @property int         $usage           用途：1-仅限一次性使用、2-可重复使用
 * @property int         $amount          金额，单位分
 * @property float       $discount        折扣
 * @property int         $rule            使用限制，单位分
 * @property int         $available_start 有效期开始
 * @property int         $available_end   有效期结束
 * @property int         $status          状态：0-未使用、1-已使用、2-已失效
 * @property Carbon|null $created_at      创建时间
 * @property Carbon|null $updated_at      最后更新时间
 * @property Carbon|null $deleted_at      删除时间
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon newQuery()
 * @method static Builder|Coupon onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon type($type)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereAvailableEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereAvailableStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereSn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coupon whereUsage($value)
 * @method static Builder|Coupon withTrashed()
 * @method static Builder|Coupon withoutTrashed()
 */
class Coupon extends Model
{
	use SoftDeletes;

	protected $table = 'coupon';
	protected $primaryKey = 'id';
	protected $dates = ['deleted_at'];

	// 筛选类型
	function scopeType($query, $type)
	{
		return $query->where('type', $type);
	}

	function getAmountAttribute($value)
	{
		return $value/100;
	}

	function setAmountAttribute($value)
	{
		$this->attributes['amount'] = $value*100;
	}

	function getDiscountAttribute($value)
	{
		return $value*10;
	}

	function setDiscountAttribute($value)
	{
		$this->attributes['discount'] = $value/10;
	}

	function getRuleAttribute($value)
	{
		return $value/100;
	}

	function setRuleAttribute($value)
	{
		$this->attributes['rule'] = $value*100;
	}
}