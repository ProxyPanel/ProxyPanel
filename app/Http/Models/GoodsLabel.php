<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 商品标签
 * Class GoodsLabel
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int        $id
 * @property int        $goods_id 商品ID
 * @property int        $label_id 标签ID
 * @property-read Goods $goods
 * @method static Builder|GoodsLabel newModelQuery()
 * @method static Builder|GoodsLabel newQuery()
 * @method static Builder|GoodsLabel query()
 * @method static Builder|GoodsLabel whereGoodsId($value)
 * @method static Builder|GoodsLabel whereId($value)
 * @method static Builder|GoodsLabel whereLabelId($value)
 */
class GoodsLabel extends Model
{
	public $timestamps = FALSE;
	protected $table = 'goods_label';
	protected $primaryKey = 'id';

	function goods()
	{
		return $this->hasOne(Goods::class, 'id', 'goods_id');
	}
}