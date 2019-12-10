<?php

namespace App\Http\Models;

use Auth;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * 工单
 * Class Ticket
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property-read mixed $status_label
 */
class Ticket extends Model
{
	protected $table = 'ticket';
	protected $primaryKey = 'id';

	function scopeUid($query)
	{
		return $query->where('user_id', Auth::user()->id);
	}

	function user()
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	function getStatusLabelAttribute()
	{
		switch($this->attributes['status']){
			case 0:
				$status_label = '<span class="badge badge-lg badge-success">'.trans('home.ticket_table_status_wait').'</span>';
				break;
			case 1:
				$status_label = '<span class="badge badge-lg badge-danger">'.trans('home.ticket_table_status_reply').'</span>';
				break;
			case 2:
				$status_label = '<span class="badge badge-lg badge-default">'.trans('home.ticket_table_status_close').'</span>';
				break;
			default:
				$status_label = '<span class="badge badge-lg badge-default"> 未知 </span>';
		}

		return $status_label;
	}

}