<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户流量记录
 * Class UserTrafficLog
 *
 * @package App\Http\Models
 * @mixin Eloquent
 */
class UserTrafficLog extends Model
{
	protected $table = 'user_traffic_log';
	protected $primaryKey = 'id';
	public $timestamps = FALSE;

	// 关联账号
	function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	// 关联节点
	function node()
	{
		return $this->belongsTo(SsNode::class, 'node_id', 'id');
	}

}