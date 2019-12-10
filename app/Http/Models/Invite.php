<?php

namespace App\Http\Models;

use Auth;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 邀请码
 * Class Invite
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property-read mixed $status_label
 */
class Invite extends Model
{
	use SoftDeletes;

	protected $table = 'invite';
	protected $primaryKey = 'id';
	protected $dates = ['deleted_at'];

	function scopeUid($query)
	{
		return $query->where('uid', Auth::user()->id);
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