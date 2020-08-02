<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 文章
 *
 * @property int                             $id
 * @property int|null                        $type       类型：1-文章、2-站内公告、3-站外公告
 * @property string                          $title      标题
 * @property string|null                     $author     作者
 * @property string|null                     $summary    简介
 * @property string|null                     $logo       LOGO
 * @property string|null                     $content    内容
 * @property int                             $sort       排序
 * @property \Illuminate\Support\Carbon      $created_at 创建时间
 * @property \Illuminate\Support\Carbon      $updated_at 最后更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 删除时间
 * @method static Builder|Article newModelQuery()
 * @method static Builder|Article newQuery()
 * @method static Builder|Article onlyTrashed()
 * @method static Builder|Article query()
 * @method static Builder|Article type($type)
 * @method static Builder|Article whereAuthor($value)
 * @method static Builder|Article whereContent($value)
 * @method static Builder|Article whereCreatedAt($value)
 * @method static Builder|Article whereDeletedAt($value)
 * @method static Builder|Article whereId($value)
 * @method static Builder|Article whereLogo($value)
 * @method static Builder|Article whereSort($value)
 * @method static Builder|Article whereSummary($value)
 * @method static Builder|Article whereTitle($value)
 * @method static Builder|Article whereType($value)
 * @method static Builder|Article whereUpdatedAt($value)
 * @method static Builder|Article withTrashed()
 * @method static Builder|Article withoutTrashed()
 * @mixin \Eloquent
 */
class Article extends Model {
	use SoftDeletes;

	protected $table = 'article';
	protected $dates = ['deleted_at'];

	// 筛选类型
	public function scopeType($query, $type) {
		return $query->whereType($type);
	}
}
