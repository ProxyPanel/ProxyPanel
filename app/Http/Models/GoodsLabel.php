<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * 商品标签
 * Class GoodsLabel
 *
 * @package App\Http\Models
 * @mixin Eloquent
 */
class GoodsLabel extends Model
{
	protected $table = 'goods_label';
	protected $primaryKey = 'id';
	public $timestamps = FALSE;

	function goods()
	{
		return $this->hasOne(Goods::class, 'id', 'goods_id');
	}
}