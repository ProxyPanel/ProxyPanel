<?php

namespace App\Http\Models;

use Auth;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * 邀请码
 * Class Invite
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property-read mixed  $status_label
 * @property int         $id
 * @property int         $uid        邀请人ID
 * @property int         $fuid       受邀人ID
 * @property string      $code       邀请码
 * @property int         $status     邀请码状态：0-未使用、1-已使用、2-已过期
 * @property string|null $dateline   有效期至
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at 删除时间
 * @property-read User   $generator
 * @property-read User   $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|Invite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invite newQuery()
 * @method static Builder|Invite onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Invite query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|Invite uid()
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereDateline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereFuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereUpdatedAt($value)
 * @method static Builder|Invite withTrashed()
 * @method static Builder|Invite withoutTrashed()
 */
class Invite extends Model
{
	use SoftDeletes;

	protected $table = 'invite';
	protected $primaryKey = 'id';
	protected $dates = ['deleted_at'];

	function scopeUid($query)
	{
		return $query->whereUid(Auth::user()->id);
	}

	function generator()
	{
		return $this->hasOne(User::class, 'id', 'uid');
	}

	function user()
	{
		return $this->hasOne(User::class, 'id', 'fuid');
	}

	function getStatusLabelAttribute()
	{
		switch($this->attributes['status']){
			case 0:
				$status_label = '<span class="badge badge-success">'.trans('home.invite_code_table_status_un').'</span>';
				break;
			case 1:
				$status_label = '<span class="badge badge-danger">'.trans('home.invite_code_table_status_yes').'</span>';
				break;
			case 2:
				$status_label = '<span class="badge badge-default">'.trans('home.invite_code_table_status_expire').'</span>';
				break;
			default:
				$status_label = '<span class="badge badge-default"> 未知 </span>';
		}

		return $status_label;
	}

}