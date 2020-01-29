<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 敏感词
 * Class SensitiveWords
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int    $id
 * @property int    $type  类型：1-黑名单、2-白名单
 * @property string $words 敏感词
 * @method static Builder|SensitiveWords newModelQuery()
 * @method static Builder|SensitiveWords newQuery()
 * @method static Builder|SensitiveWords query()
 * @method static Builder|SensitiveWords whereId($value)
 * @method static Builder|SensitiveWords whereType($value)
 * @method static Builder|SensitiveWords whereWords($value)
 */
class SensitiveWords extends Model
{
	public $timestamps = FALSE;
	protected $table = 'sensitive_words';
	protected $primaryKey = 'id';
}