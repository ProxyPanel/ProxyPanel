<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 工单
 *
 * @property int                             $id
 * @property int                             $user_id    用户ID
 * @property int|null                        $admin_id   管理员ID
 * @property string                          $title      标题
 * @property string                          $content    内容
 * @property int                             $status     状态：0-待处理、1-已处理未关闭、2-已关闭
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 最后更新时间
 * @property-read \App\Models\User|null      $admin
 * @property-read string                     $status_label
 * @property-read \App\Models\User|null      $user
 * @method static Builder|Ticket newModelQuery()
 * @method static Builder|Ticket newQuery()
 * @method static Builder|Ticket query()
 * @method static Builder|Ticket uid()
 * @method static Builder|Ticket whereAdminId($value)
 * @method static Builder|Ticket whereContent($value)
 * @method static Builder|Ticket whereCreatedAt($value)
 * @method static Builder|Ticket whereId($value)
 * @method static Builder|Ticket whereStatus($value)
 * @method static Builder|Ticket whereTitle($value)
 * @method static Builder|Ticket whereUpdatedAt($value)
 * @method static Builder|Ticket whereUserId($value)
 * @mixin \Eloquent
 */
class Ticket extends Model {
	protected $table = 'ticket';

	public function scopeUid($query) {
		return $query->whereUserId(Auth::id());
	}

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function admin(): HasOne {
		return $this->hasOne(User::class, 'id', 'admin_id');
	}

	public function getStatusLabelAttribute(): string {
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
