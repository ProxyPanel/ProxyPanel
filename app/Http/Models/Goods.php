<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * 商品
 * Class Goods
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int                          $id
 * @property string                       $sku        商品服务SKU
 * @property string                       $name       商品名称
 * @property string                       $logo       商品图片地址
 * @property int                          $traffic    商品内含多少流量，单位MiB
 * @property int                          $type       商品类型：1-流量包、2-套餐、3-余额充值
 * @property int                          $price      售价，单位分
 * @property int                          $renew      流量重置价格，单位分
 * @property int                          $period     流量自动重置周期
 * @property string|null                  $info       商品
 * @property string|null                  $desc       商品描述
 * @property int                          $days       有效期
 * @property int                          $invite_num 赠送邀请码数
 * @property int                          $limit_num  限购数量，默认为0不限购
 * @property string                       $color      商品颜色
 * @property int                          $sort       排序
 * @property int                          $is_hot     是否热销：0-否、1-是
 * @property int                          $status     状态：0-下架、1-上架
 * @property Carbon|null                  $created_at 创建时间
 * @property Carbon|null                  $updated_at 最后更新时间
 * @property Carbon|null                  $deleted_at 删除时间
 * @property-read mixed                   $traffic_label
 * @property-read Collection|GoodsLabel[] $label
 * @property-read int|null                $label_count
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|Goods newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Goods newQuery()
 * @method static Builder|Goods onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Goods query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|Goods type($type)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereInviteNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereIsHot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereLimitNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereRenew($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereTraffic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereUpdatedAt($value)
 * @method static Builder|Goods withTrashed()
 * @method static Builder|Goods withoutTrashed()
 */
class Goods extends Model
{
	use SoftDeletes;
	protected $table = 'goods';
	protected $primaryKey = 'id';
	protected $dates = ['deleted_at'];

	function scopeType($query, $type)
	{
		return $query->whereType($type)->whereStatus(1)->orderBy('sort', 'desc');
	}

	function label()
	{
		return $this->hasMany(GoodsLabel::class, 'goods_id', 'id');
	}

	function getPriceAttribute($value)
	{
		return $value/100;
	}

	function setPriceAttribute($value)
	{
		$this->attributes['price'] = $value*100;
	}

	function getRenewAttribute($value)
	{
		return $value/100;
	}

	function setRenewAttribute($value)
	{
		return $this->attributes['renew'] = $value*100;
	}

	function getTrafficLabelAttribute()
	{
		return flowAutoShow($this->attributes['traffic']*1048576);
	}
}
