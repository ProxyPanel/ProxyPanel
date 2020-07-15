<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 国家/地区
 *
 * @property int    $id
 * @property string $name 名称
 * @property string $code 代码
 * @method static Builder|Country newModelQuery()
 * @method static Builder|Country newQuery()
 * @method static Builder|Country query()
 * @method static Builder|Country whereCode($value)
 * @method static Builder|Country whereId($value)
 * @method static Builder|Country whereName($value)
 * @mixin \Eloquent
 */
class Country extends Model {
	public $timestamps = false;
	protected $table = 'country';
}
