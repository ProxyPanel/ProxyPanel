<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 标签
 */
class Label extends Model {
	public $timestamps = false;
	protected $table = 'label';

	public function nodes(): HasMany {
		return $this->hasMany(NodeLabel::class);
	}
}
