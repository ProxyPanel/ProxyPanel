<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 标签
 * Class Label
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int    $id
 * @property string $name 名称
 * @property int    $sort 排序值
 * @method static Builder|Label newModelQuery()
 * @method static Builder|Label newQuery()
 * @method static Builder|Label query()
 * @method static Builder|Label whereId($value)
 * @method static Builder|Label whereName($value)
 * @method static Builder|Label whereSort($value)
 */
class Label extends Model
{
	public $timestamps = FALSE;
	protected $table = 'label';
	protected $primaryKey = 'id';
}