<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 账号余额操作日志
 *
 * @property int                        $id
 * @property int                        $user_id     账号ID
 * @property int                        $order_id    订单ID
 * @property int                        $before      发生前余额，单位分
 * @property int                        $after       发生后金额，单位分
 * @property int                        $amount      发生金额，单位分
 * @property string|null                $description 操作描述
 * @property string|null                $created_at  创建时间
 * @property-read \App\Models\User|null $user
 * @method static Builder|UserCreditLog newModelQuery()
 * @method static Builder|UserCreditLog newQuery()
 * @method static Builder|UserCreditLog query()
 * @method static Builder|UserCreditLog whereAfter($value)
 * @method static Builder|UserCreditLog whereAmount($value)
 * @method static Builder|UserCreditLog whereBefore($value)
 * @method static Builder|UserCreditLog whereCreatedAt($value)
 * @method static Builder|UserCreditLog whereDescription($value)
 * @method static Builder|UserCreditLog whereId($value)
 * @method static Builder|UserCreditLog whereOrderId($value)
 * @method static Builder|UserCreditLog whereUserId($value)
 * @mixin \Eloquent
 */
class UserCreditLog extends Model {
	public $timestamps = false;
	protected $table = 'user_credit_log';

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function getBeforeAttribute($value) {
		return $value / 100;
	}

	public function setBeforeAttribute($value) {
		return $this->attributes['before'] = $value * 100;
	}

	public function getAfterAttribute($value) {
		return $value / 100;
	}

	public function setAfterAttribute($value) {
		return $this->attributes['after'] = $value * 100;
	}

	public function getAmountAttribute($value) {
		return $value / 100;
	}

	public function setAmountAttribute($value) {
		return $this->attributes['amount'] = $value * 100;
	}
}
