<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 系统配置
 *
 * @property int         $id
 * @property string      $name  配置名
 * @property string|null $value 配置值
 * @method static Builder|Config newModelQuery()
 * @method static Builder|Config newQuery()
 * @method static Builder|Config query()
 * @method static Builder|Config whereId($value)
 * @method static Builder|Config whereName($value)
 * @method static Builder|Config whereValue($value)
 * @mixin \Eloquent
 */
class Config extends Model {
	public $timestamps = false;
	protected $table = 'config';
}
