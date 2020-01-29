<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 订单商品
 * Class OrderGoods
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property int         $oid          订单ID
 * @property string      $order_sn     订单编号
 * @property int         $user_id      用户ID
 * @property int         $goods_id     商品ID
 * @property int         $num          商品数量
 * @property int         $origin_price 商品原价，单位分
 * @property int         $price        商品实际价格，单位分
 * @property int         $is_expire    是否已过期：0-未过期、1-已过期
 * @property Carbon|null $created_at   创建时间
 * @property Carbon|null $updated_at   最后更新时间
 * @property-read Goods  $goods
 * @property-read User   $user
 * @method static Builder|OrderGoods newModelQuery()
 * @method static Builder|OrderGoods newQuery()
 * @method static Builder|OrderGoods query()
 * @method static Builder|OrderGoods whereCreatedAt($value)
 * @method static Builder|OrderGoods whereGoodsId($value)
 * @method static Builder|OrderGoods whereId($value)
 * @method static Builder|OrderGoods whereIsExpire($value)
 * @method static Builder|OrderGoods whereNum($value)
 * @method static Builder|OrderGoods whereOid($value)
 * @method static Builder|OrderGoods whereOrderSn($value)
 * @method static Builder|OrderGoods whereOriginPrice($value)
 * @method static Builder|OrderGoods wherePrice($value)
 * @method static Builder|OrderGoods whereUpdatedAt($value)
 * @method static Builder|OrderGoods whereUserId($value)
 */
class OrderGoods extends Model
{
	protected $table = 'order_goods';
	protected $primaryKey = 'id';

	function user()
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	function goods()
	{
		return $this->hasOne(Goods::class, 'id', 'goods_id');
	}

	function getOriginPriceAttribute($value)
	{
		return $value/100;
	}

	function setOriginPriceAttribute($value)
	{
		return $this->attributes['origin_price'] = $value*100;
	}

	function getPriceAttribute($value)
	{
		return $value/100;
	}

	function setPriceAttribute($value)
	{
		return $this->attributes['price'] = $value*100;
	}
}