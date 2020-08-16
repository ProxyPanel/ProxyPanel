<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 配置信息
 */
class SsConfig extends Model {
	public $timestamps = false;
	protected $table = 'ss_config';

	// 筛选默认

	public function scopeDefault($query): void {
		$query->whereIsDefault(1);
	}

	// 筛选类型
	public function scopeType($query, $type): void {
		$query->whereType($type);
	}
}
