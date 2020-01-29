<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * 文章
 * Class Article
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property string      $title      标题
 * @property string|null $author     作者
 * @property string|null $summary    简介
 * @property string|null $logo       LOGO
 * @property string|null $content    内容
 * @property int|null    $type       类型：1-文章、2-公告、3-购买说明、4-使用教程
 * @property int         $sort       排序
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 最后更新时间
 * @property Carbon|null $deleted_at 删除时间
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Article newQuery()
 * @method static Builder|Article onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Article query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|Article type($type)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereUpdatedAt($value)
 * @method static Builder|Article withTrashed()
 * @method static Builder|Article withoutTrashed()
 */
class Article extends Model
{
	use SoftDeletes;

	protected $table = 'article';
	protected $primaryKey = 'id';
	protected $dates = ['deleted_at'];

	// 筛选类型
	function scopeType($query, $type)
	{
		return $query->where('type', $type);
	}
}