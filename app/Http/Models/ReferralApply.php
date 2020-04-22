<?php

namespace App\Http\Models;

use Auth;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 返利申请
 * Class ReferralApply
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property int         $user_id    用户ID
 * @property int         $before     操作前可提现金额，单位分
 * @property int         $after      操作后可提现金额，单位分
 * @property int         $amount     本次提现金额，单位分
 * @property string      $link_logs  关联返利日志ID，例如：1,3,4
 * @property int         $status     状态：-1-驳回、0-待审核、1-审核通过待打款、2-已打款
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 最后更新时间
 * @property-read User   $User
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
 */
class ReferralApply extends Model
{
	protected $table = 'referral_apply';
	protected $primaryKey = 'id';

	function scopeUid($query)
	{
		return $query->whereUserId(Auth::user()->id);
	}

	function User()
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	function getBeforeAttribute($value)
	{
		return $value/100;
	}

	function setBeforeAttribute($value)
	{
		$this->attributes['before'] = $value*100;
	}

	function getAfterAttribute($value)
	{
		return $value/100;
	}

	function setAfterAttribute($value)
	{
		$this->attributes['after'] = $value*100;
	}

	function getAmountAttribute($value)
	{
		return $value/100;
	}

	function setAmountAttribute($value)
	{
		$this->attributes['amount'] = $value*100;
	}
}