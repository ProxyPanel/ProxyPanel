<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 商品标签
 *
 * @property-read Goods|null $goods
 * @method static Builder|GoodsLabel newModelQuery()
 * @method static Builder|GoodsLabel newQuery()
 * @method static Builder|GoodsLabel query()
 * @mixin Eloquent
 */
class GoodsLabel extends Model {
	public $timestamps = false;
	protected $table = 'goods_label';
	protected $primaryKey = 'id';

	function goods() {
		return $this->hasOne(Goods::class, 'id', 'goods_id');
	}
}
