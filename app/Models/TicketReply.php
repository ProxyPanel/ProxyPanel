<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 工单回复
 *
 * @property int                             $id
 * @property int                             $ticket_id  工单ID
 * @property int                             $user_id    回复用户的ID
 * @property int|null                        $admin_id   管理员ID
 * @property string                          $content    回复内容
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 最后更新时间
 * @property-read \App\Models\User|null      $admin
 * @property-read \App\Models\User|null      $user
 * @method static Builder|TicketReply newModelQuery()
 * @method static Builder|TicketReply newQuery()
 * @method static Builder|TicketReply query()
 * @method static Builder|TicketReply whereAdminId($value)
 * @method static Builder|TicketReply whereContent($value)
 * @method static Builder|TicketReply whereCreatedAt($value)
 * @method static Builder|TicketReply whereId($value)
 * @method static Builder|TicketReply whereTicketId($value)
 * @method static Builder|TicketReply whereUpdatedAt($value)
 * @method static Builder|TicketReply whereUserId($value)
 * @mixin \Eloquent
 */
class TicketReply extends Model {
	protected $table = 'ticket_reply';

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function admin(): HasOne {
		return $this->hasOne(User::class, 'id', 'admin_id');
	}
}
