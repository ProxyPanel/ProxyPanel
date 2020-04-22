<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 配置信息
 * Class SsConfig
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int    $id
 * @property string $name       配置名
 * @property int    $type       类型：1-加密方式、2-协议、3-混淆
 * @property int    $is_default 是否默认：0-不是、1-是
 * @property int    $sort       排序：值越大排越前
 * @method static Builder|SsConfig default()
 * @method static Builder|SsConfig newModelQuery()
 * @method static Builder|SsConfig newQuery()
 * @method static Builder|SsConfig query()
 * @method static Builder|SsConfig type($type)
 * @method static Builder|SsConfig whereId($value)
 * @method static Builder|SsConfig whereIsDefault($value)
 * @method static Builder|SsConfig whereName($value)
 * @method static Builder|SsConfig whereSort($value)
 * @method static Builder|SsConfig whereType($value)
 */
class SsConfig extends Model
{
	public $timestamps = FALSE;
	protected $table = 'ss_config';
	protected $primaryKey = 'id';

	// 筛选默认

	function scopeDefault($query)
	{
		$query->whereIsDefault(1);
	}

	// 筛选类型
	function scopeType($query, $type)
	{
		$query->whereType($type);
	}
}