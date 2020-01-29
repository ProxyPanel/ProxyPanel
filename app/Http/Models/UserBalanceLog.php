<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 账号余额操作日志
 * Class UserBalanceLog
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property int         $user_id    账号ID
 * @property int         $order_id   订单ID
 * @property int         $before     发生前余额，单位分
 * @property int         $after      发生后金额，单位分
 * @property int         $amount     发生金额，单位分
 * @property string|null $desc       操作描述
 * @property string|null $created_at 创建时间
 * @property-read User   $user
 * @method static Builder|UserBalanceLog newModelQuery()
 * @method static Builder|UserBalanceLog newQuery()
 * @method static Builder|UserBalanceLog query()
 * @method static Builder|UserBalanceLog whereAfter($value)
 * @method static Builder|UserBalanceLog whereAmount($value)
 * @method static Builder|UserBalanceLog whereBefore($value)
 * @method static Builder|UserBalanceLog whereCreatedAt($value)
 * @method static Builder|UserBalanceLog whereDesc($value)
 * @method static Builder|UserBalanceLog whereId($value)
 * @method static Builder|UserBalanceLog whereOrderId($value)
 * @method static Builder|UserBalanceLog whereUserId($value)
 */
class UserBalanceLog extends Model
{
	public $timestamps = FALSE;
	protected $table = 'user_balance_log';
	protected $primaryKey = 'id';

	function user()
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	function getBeforeAttribute($value)
	{
		return $value/100;
	}

	function setBeforeAttribute($value)
	{
		return $this->attributes['before'] = $value*100;
	}

	function getAfterAttribute($value)
	{
		return $value/100;
	}

	function setAfterAttribute($value)
	{
		return $this->attributes['after'] = $value*100;
	}

	function getAmountAttribute($value)
	{
		return $value/100;
	}

	function setAmountAttribute($value)
	{
		return $this->attributes['amount'] = $value*100;
	}
}