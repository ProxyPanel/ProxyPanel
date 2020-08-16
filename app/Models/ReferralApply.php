<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 返利申请
 */
class ReferralApply extends Model {
	protected $table = 'referral_apply';
	protected $casts = [
		'link_logs' => 'array'
	];

	public function scopeUid($query) {
		return $query->whereUserId(Auth::id());
	}

	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}

	public function getBeforeAttribute($value) {
		return $value / 100;
	}

	public function setBeforeAttribute($value): void {
		$this->attributes['before'] = $value * 100;
	}

	public function getAfterAttribute($value) {
		return $value / 100;
	}

	public function setAfterAttribute($value): void {
		$this->attributes['after'] = $value * 100;
	}

	public function getAmountAttribute($value) {
		return $value / 100;
	}

	public function setAmountAttribute($value): void {
		$this->attributes['amount'] = $value * 100;
	}
}
