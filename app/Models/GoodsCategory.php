<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsCategory extends Model
{
    protected $table = 'goods_category';
    protected $guarded = [];

    public function goods(): HasMany
    {
        return $this->hasMany(Goods::class, 'category_id');
    }
}
