<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户订阅地址请求日志
 * Class UserSubscribeLog
 *
 * @package App\Http\Models
 * @mixin Eloquent
 */
class UserSubscribeLog extends Model
{
	protected $table = 'user_subscribe_log';
	protected $primaryKey = 'id';
	public $timestamps = FALSE;

	function user()
	{
		return $this->hasManyThrough(User::class, UserSubscribe::class, 'id', 'id', 'sid', 'user_id');
	}
}