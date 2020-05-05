<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 等级
 *
 * @method static Builder|Level newModelQuery()
 * @method static Builder|Level newQuery()
 * @method static Builder|Level query()
 * @mixin Eloquent
 */
class Level extends Model {
	public $timestamps = false;
	protected $table = 'level';
	protected $primaryKey = 'id';
}
