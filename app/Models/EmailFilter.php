<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 邮箱后缀过滤
 *
 * @property int    $id
 * @property int    $type  类型：1-黑名单、2-白名单
 * @property string $words 邮箱后缀
 * @method static Builder|EmailFilter newModelQuery()
 * @method static Builder|EmailFilter newQuery()
 * @method static Builder|EmailFilter query()
 * @method static Builder|EmailFilter whereId($value)
 * @method static Builder|EmailFilter whereType($value)
 * @method static Builder|EmailFilter whereWords($value)
 * @mixin \Eloquent
 */
class EmailFilter extends Model {
	public $timestamps = false;
	protected $table = 'email_filter';
}
