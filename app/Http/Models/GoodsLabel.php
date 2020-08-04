<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 商品标签
 * Class GoodsLabel
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class GoodsLabel extends Model
{
    protected $table = 'goods_label';
    protected $primaryKey = 'id';
    public $timestamps = false;

    function goods()
    {
        return $this->hasOne(Goods::class, 'id', 'goods_id');
    }
}