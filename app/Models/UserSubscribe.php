<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 用户订阅地址
 */
class UserSubscribe extends Model {
	protected $table = 'user_subscribe';
	protected $guarded = ['id', 'user_id'];

	public function scopeUid($query) {
		return $query->whereUserId(Auth::id());
	}

	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}

	public function userSubscribeLogs(): HasMany {
		return $this->hasMany(UserSubscribeLog::class);
	}
}
