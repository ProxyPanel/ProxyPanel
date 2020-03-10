<?php

namespace App\Http\Models;

use Auth;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 工单
 * Class Ticket
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property int         $user_id
 * @property string      $title      标题
 * @property string      $content    内容
 * @property int         $status     状态：0-待处理、1-已处理未关闭、2-已关闭
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 最后更新时间
 * @property-read mixed  $status_label
 * @property-read User   $user
 * @method static Builder|Ticket newModelQuery()
 * @method static Builder|Ticket newQuery()
 * @method static Builder|Ticket query()
 * @method static Builder|Ticket uid()
 * @method static Builder|Ticket whereContent($value)
 * @method static Builder|Ticket whereCreatedAt($value)
 * @method static Builder|Ticket whereId($value)
 * @method static Builder|Ticket whereStatus($value)
 * @method static Builder|Ticket whereTitle($value)
 * @method static Builder|Ticket whereUpdatedAt($value)
 * @method static Builder|Ticket whereUserId($value)
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