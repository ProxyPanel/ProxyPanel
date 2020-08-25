<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 等级
 */
class Level extends Model {
	public $timestamps = false;
	protected $table = 'level';
	protected $guarded = ['id'];
}