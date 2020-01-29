<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 等级
 * Class Level
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int    $id
 * @property int    $level      等级
 * @property string $level_name 等级名称
 * @method static Builder|Level newModelQuery()
 * @method static Builder|Level newQuery()
 * @method static Builder|Level query()
 * @method static Builder|Level whereId($value)
 * @method static Builder|Level whereLevel($value)
 * @method static Builder|Level whereLevelName($value)
 */
class Level extends Model
{
	public $timestamps = FALSE;
	protected $table = 'level';
	protected $primaryKey = 'id';
}