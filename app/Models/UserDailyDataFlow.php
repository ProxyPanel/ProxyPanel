<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 用户每日流量统计
 */
class UserDailyDataFlow extends Model {
	public const UPDATED_AT = null;
	protected $table = 'user_daily_data_flow';

	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}

	public function node(): BelongsTo {
		return $this->belongsTo(Node::class);
	}

	// 用户每天使用总流量
	public function scopeUserDaily($query, $uid) {
		return $query->whereUserId($uid)->whereNodeId(0);
	}
}
