<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 工单回复
 * Class TicketReply
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property int         $ticket_id  工单ID
 * @property int         $user_id    回复人ID
 * @property string      $content    回复内容
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 最后更新时间
 * @property-read User   $user
 * @method static Builder|TicketReply newModelQuery()
 * @method static Builder|TicketReply newQuery()
 * @method static Builder|TicketReply query()
 * @method static Builder|TicketReply whereContent($value)
 * @method static Builder|TicketReply whereCreatedAt($value)
 * @method static Builder|TicketReply whereId($value)
 * @method static Builder|TicketReply whereTicketId($value)
 * @method static Builder|TicketReply whereUpdatedAt($value)
 * @method static Builder|TicketReply whereUserId($value)
 */
class TicketReply extends Model
{
	protected $table = 'ticket_reply';
	protected $primaryKey = 'id';

	function user()
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}