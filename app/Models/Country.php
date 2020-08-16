<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 国家/地区
 */
class Country extends Model {
	public $timestamps = false;
	public $incrementing = false;
	protected $table = 'country';
	protected $primaryKey = 'code';
	protected $keyType = 'string';
	protected $fillable = ['*'];
}
