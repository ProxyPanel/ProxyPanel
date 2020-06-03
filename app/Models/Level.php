<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 等级
 *
 * @property int    $id
 * @property int    $level 等级
 * @property string $name  等级名称
 * @method static Builder|Level newModelQuery()
 * @method static Builder|Level newQuery()
 * @method static Builder|Level query()
 * @method static Builder|Level whereId($value)
 * @method static Builder|Level whereLevel($value)
 * @method static Builder|Level whereName($value)
 * @mixin \Eloquent
 */
class Level extends Model {
	public $timestamps = false;
	protected $table = 'level';
	protected $primaryKey = 'id';
}