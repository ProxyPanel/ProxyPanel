<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 商品
 */
class Goods extends Model {
	use SoftDeletes;

	protected $table = 'goods';
	protected $dates = ['deleted_at'];

	public function scopeType($query, $type) {
		return $query->whereType($type)->whereStatus(1)->orderByDesc('sort');
	}

	public function getPriceAttribute($value) {
		return $value / 100;
	}

	public function setPriceAttribute($value): void {
		$this->attributes['price'] = $value * 100;
	}

	public function getRenewAttribute($value) {
		return $value / 100;
	}

	public function setRenewAttribute($value) {
		return $this->attributes['renew'] = $value * 100;
	}

	public function getTrafficLabelAttribute() {
		return flowAutoShow($this->attributes['traffic'] * MB);
	}
}
